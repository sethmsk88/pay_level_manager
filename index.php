<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $pageTitle="Pay Level Manager"; ?></title>

    <!-- Linked stylesheets -->
    <link href="/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/bootstrap/scripts/DataTables-1.10.7/media/css/jquery.dataTables.css" rel="stylesheet">
    <link href="../css/master.css" rel="stylesheet">
    <link href="./css/main.css" rel="stylesheet">

    <!-- Included PHP Libraries -->
    <?php include $_SERVER['DOCUMENT_ROOT'] . '\bootstrap\libraries-php\stats.php'; ?>

    <!-- Included UDFs -->
    <?php include "../shared/query_UDFs.php"; ?>

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
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="/bootstrap/js/bootstrap.min.js"></script>

    <!-- Included Scripts -->
    <script src="./scripts/main.js"></script>
    <script src="/bootstrap/js/money_formatting.js"></script>
    <script src="/bootstrap/scripts/DataTables-1.10.7/media/js/jquery.datatables.js"></script>

    <?php

        // Include my database info
        include "../shared/dbInfo.php";

        // Set application homepage
        $homepage = "homepage";

        // Globals
        $payLevels_table = "pay_levels";
        
        // If a page variable exists, include the page
    	if (isset($_GET["page"])){
    		$filePath = './content/' . $_GET["page"] . '.php';
    	}
    	else{
    		$filePath = './content/' . $homepage . '.php';
    	}

        // Include Header
        $headerText = "Pay Level Manager";
        include "../templates/header_2.php";

    	if (file_exists($filePath)){
			include $filePath;
		}
		else{
			echo '<h2>404 Error</h2>Page does not exist';
		}

    ?>




  </body>
</html>
