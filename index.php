<?php
	$APP_homepage = "homepage";

	define("APP_NAME", "Pay Level Manager");
	define("APP_PATH", "http://" . $_SERVER['HTTP_HOST'] . "./bootstrap/apps/login_system/");

	include_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/dbInfo.php';
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo APP_NAME; ?></title>

    <!-- Linked stylesheets -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bootstrap/scripts/DataTables-1.10.7/media/css/jquery.dataTables.css" rel="stylesheet">
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
    <link href="../css/navbar-custom1.css" rel="stylesheet">
    <link href="../css/master.css" rel="stylesheet">
    <link href="./css/main.css" rel="stylesheet">

    <!-- Included PHP Libraries -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '\bootstrap\libraries-php\stats.php'; ?>

    <!-- Included UDFs -->
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '\bootstrap\apps\shared\query_UDFs.php'; ?>

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/bootstrap/js/bootstrap.min.js"></script>

    <!-- Included Scripts -->
    <script src="./scripts/main.js"></script>
    <script src="/bootstrap/js/money_formatting.js"></script>
    <script src="/bootstrap/scripts/DataTables-1.10.7/media/js/jquery.datatables.js"></script>

    <?php
    	// Include FAMU logo header
        include "../templates/header_3.php";
    ?>

	<!-- Nav Bar -->
	<nav
        id="pageNavBar"
        class="navbar navbar-default navbar-custom1 navbar-static-top"
        role="navigation"
        >
        <div class="container">
            <div class="navbar-header">
                <button
                    type="button"
                    class="navbar-toggle"
                    data-toggle="collapse"
                    data-target="#navbarCollapse"
                    >
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><?php echo APP_NAME; ?></a>
            </div>
            <div id="navbarCollapse" class="collapse navbar-collapse">
                <!-- Nav links -->
                <ul class="nav navbar-nav">
                    <li id="homepage-link">
                        <?php echo '<a id="navLink-login" href="./?page=homepage">Homepage</a>'; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>


    <?php
        // If a page variable exists, include the page
    	if (isset($_GET["page"])){
    		$filePath = './content/' . $_GET["page"] . '.php';
    	}
    	else{
    		$filePath = './content/' . $APP_homepage . '.php';
    	}

    	if (file_exists($filePath)){
			include $filePath;
		}
		else{
			echo '<h2>404 Error</h2>Page does not exist';
		}
    ?>

  </body>
</html>
