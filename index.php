<?php
$servername = "localhost";
$username = "root";
$password = "";
try {
  $pdo = new PDO("mysql:host=${servername};dbname=php_first_try", $username, $password);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e){
  echo $e->getMessage();
}

# This PHP logic handles user actions
# New TODO
if (isset($_POST['submit'])) 
{
  $description = $_POST['description'];
  $schedule_date = date("Y-m-d", strtotime($_POST['schedule_date']));
  $sth = $pdo->prepare("INSERT INTO todos (title,schedule_date) VALUES (:description,:schedule_date)");
  $sth->bindValue(':description', $description, PDO::PARAM_STR);
  $sth->bindValue(':schedule_date', $schedule_date, PDO::PARAM_STR);
  $sth->execute();
}
# Delete TODO
elseif (isset($_POST['delete']))
{ 
  $id = $_POST['id'];
  $sth = $pdo->prepare("delete from todos where id = :id");
  $sth->bindValue(':id', $id, PDO::PARAM_INT);
  $sth->execute();
}
# Update completion status
elseif (isset($_POST['complete']))
{
  $id = $_POST['id'];
  $sth = $pdo->prepare("UPDATE todos SET complete = 1 where id = :id");
  $sth->bindValue(':id', $id, PDO::PARAM_INT);
  $sth->execute();
}

elseif (isset($_POST['undo'])) {
  $id = $_POST['id'];
  $statment = $pdo->prepare("UPDATE todos SET complete = 0 where id = :id");
  $statment->bindValue(':id', $id, PDO::PARAM_INT);
  $statment->execute();
}
# Here is the HTML:
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <title>Todo List</title>
  <script type="text/javascript" src="https://code.jquery.com/jquery-1.11.3.min.js"></script>
  <!-- Isolated Version of Bootstrap, not needed if your site already uses Bootstrap -->
  <link rel="stylesheet" href="https://formden.com/static/cdn/bootstrap-iso.css" />
  <!-- Bootstrap Date-Picker Plugin -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css"/>
</head>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <a class="navbar-brand" href="#">Navbar</a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <div class="collapse navbar-collapse" id="navbarSupportedContent">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="#">Link</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          Dropdown
        </a>
        <div class="dropdown-menu" aria-labelledby="navbarDropdown">
          <a class="dropdown-item" href="#">Action</a>
          <a class="dropdown-item" href="#">Another action</a>
          <div class="dropdown-divider"></div>
          <a class="dropdown-item" href="#">Something else here</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link disabled" href="#" tabindex="-1" aria-disabled="true">Disabled</a>
      </li>
    </ul>
    <form class="form-inline my-2 my-lg-0">
      <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>
    </form>
  </div>
</nav>

<body class="container">
  <h4 class="mt-4">Create your tasks</h4>
  <form method="post" action="" class="form-inline">
    <div class="mt-4 mb-5 mr-3">
      <input type="text" name="schedule_date" value="" class="form-control" placeholder="Choose date">
    </div>
    <div class="mt-4 mb-5">
      <input type="text" name="description" value="" class="form-control" placeholder="Name of todo">
    </div>
    <div class="mt-4 mb-5 ml-4">
      <input type="submit" name="submit" value="Add to list" class="btn btn-large btn-info" />
    </div>
  </form>
  <h2>Todos</h2>
  <table class="table table-striped">
    <thead>
      <th>Date</th>
      <th>Task</th>
      <th colspan="2">Actions</th>
    </thead>
    <tbody>

      <?php
        # Entering PHP mode, 
        $sth = $pdo->prepare("SELECT * FROM todos ORDER BY schedule_date ASC");
        $sth->execute();

        foreach ($sth as $row) {
        # Exiting PHP Mode
      ?> 
      <tr>
        <td>
          <?= $row['schedule_date'] ?>
        </td>
        <td>
          <?=htmlspecialchars($row['title'])?>
        </td>
        <td>
          <form method="POST" class="form-inline">
            <?php # Here we are mixing HTML and PHP to get the desired document
              if (!$row['complete']) {
            ?>
            <button type="submit" name="complete" class="btn btn-secondary">Complete</button>
            <input type="hidden" name="id" value="<?= $row['id'] ?> ">
            <?php
              } else {
            ?>
            <p class="text-muted">Task Complete!</p>
            <button type="Undo" name="undo" value="<?= $row['id'] ?>" class="btn btn-info ml-2">Undo</button>
            <?php
              }
            ?>
            <button type="submit" name="delete" class="ml-3 btn btn-danger">Delete</button>
            <input type="hidden" name="id" value="<?=$row['id']?>">
          </form>
        </td>
      </tr>
      <?php
      }
      ?>    
    </tbody>
  </table>
</body>
</html>
<script>
  $(document).ready(function(){
    var date_input=$('input[name="schedule_date"]'); //our date input has the name "date"
    var container=$('.form-inline').length>0 ? $('.form-inline').parent() : "body";
    var options={
      format: 'mm/dd/yyyy',
      container: container,
      todayHighlight: true,
      autoclose: true,
    };
    date_input.datepicker(options);
  })
</script>
