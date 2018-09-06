<?php
	require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/class_specs/vendor/autoload.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/db_connect.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/shared/login_functions.php';;

    require_once "./includes/functions.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . '/bootstrap/apps/pay_level_manager/includes/globals.php';

    // Start session or regenerate session id
    sec_session_start();

    // Check to see if User is logged in
    login_check($GLOBALS['APP_ID'], $conn);
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $GLOBALS['APP_NAME']; ?></title>

    <!-- Linked stylesheets -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
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
    <!-- Google Analytics Tracking -->
    <?php include_once($_SERVER['DOCUMENT_ROOT'] . "\bootstrap\apps\shared\analyticstracking.php") ?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/bootstrap/js/bootstrap.min.js"></script>

    <!-- Included Scripts -->
    <script src="./scripts/main.js"></script>
    <script src="./scripts/login.js"></script>
    <script src="/bootstrap/js/money_formatting.js"></script>
    <script src="/bootstrap/scripts/DataTables-1.10.7/media/js/jquery.datatables.js"></script>
    <script src="/bootstrap/js/sha512.js"></script>

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
                <a class="navbar-brand" href="#"><?php echo $GLOBALS['APP_NAME']; ?></a>
            </div>
            <div id="navbarCollapse" class="collapse navbar-collapse">
                <!-- Nav links -->
                <ul class="nav navbar-nav">
                    <li id="homepage-link">
                        <a id="navLink-login" href="./?page=homepage">Homepage</a>
                    </li>
                    <li>
                        <a href="./?page=pay_level_ranges">Pay Level Ranges</a>
                    </li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <?php if ($GLOBALS['LOGGED_IN'] AND !is_null($GLOBALS['ACCESS_LEVEL'])) { ?>
                    <li class="dropdown" style="cursor:pointer;">
                        <a href="#" data-toggle="dropdown" class="dropdown-toggle"><span class="glyphicon glyphicon-user" style="margin-right:8px;"></span><?php echo $_SESSION['firstName']; ?> <span class="glyphicon glyphicon-triangle-bottom" style="margin-left:4px;"></span></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a id="settings-link" href="?page=settings">Settings</a>
                            </li>
                            <li>
                                <?php
                                    $redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                                    $logoutUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/bootstrap/apps/shared/act_logout.php?redirect=' . $redirectUrl;
                                ?>
                                <a id="logout-link" href="<?= $logoutUrl ?>"> Log out</a>
                            </li>
                        </ul>
                    </li>
                    <?php } else { ?>
                    <li>
                        <div class="dropdown">
                            <a href="#" data-toggle="dropdown" class="dropdown-toggle">Log in</a>
                            <ul class="dropdown-menu" style="padding:0px;">
                                <li>
                                    <?php include_once './includes/inc_login_form.php'; ?>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <?php } ?>
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
    		$filePath = './content/' . $GLOBALS['APP_HOMEPAGE'] . '.php';
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
