<?php

include 'VarnishlogParser.class.php';
include 'kint/Kint.class.php';

$FILEPATH = "";
$error = "";
$transactions_list = "";
$transactions_string = "";
$transactions_diagram_url = "";
try {
  // Check requirements
  if(!class_exists("VarnishlogParser\VarnishlogParser"))
    throw new Exception("Please, include VarnishlogParser in this directory!");
  if(!class_exists("\Kint"))
    throw new Exception("Please, include Kint library.");

  // Check input file
  if(empty($_REQUEST["filepath"]))
    throw new InvalidArgumentException("No filepath provided");

  $FILEPATH = $_REQUEST["filepath"]; // Obvious XSS flaw here

  // Parse Varnishlog file
  $transactions_list = VarnishlogParser\VarnishlogParser::parse($FILEPATH);
  // Output text representation of transactions
  $transactions_string = VarnishlogParser\VarnishlogParser::simpleAnalysis($transactions_list,1);
  // Get URL for sequence diagram
  $transactions_diagram_url = VarnishlogParser\VarnishlogParser::getSequenceDiagram($transactions_string);
  if(!$transactions_diagram_url)
    throw new Exception("Error while generating image with websequencediagrams.com.");
}
catch(\InvalidArgumentException $e){
  $error = "";
}
catch(\Exception $e){
  $error = $e->getMessage();
}

?><!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Varnishlog analysis for : <?php echo $FILEPATH ?></title>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <div class="container">
    <div class="jumbotron">
      <h1>Varnishlog Analysis</h1>
      <?php if($FILEPATH): ?>
        <p><em><?php echo $FILEPATH // Obvious XSS flaw here ?></em></p>
        <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
          <button type="submit" class="btn btn-primary">Try another file</button>
        </form>
      <?php endif; ?>
    </div>

    <?php if(empty($FILEPATH)) : ?>
      <!-- No filepath provided -->
      <form method="get" action="<?php echo $_SERVER['PHP_SELF']?>">
        <div class="form-group">
          <label for="filepath">Path of varnishlog file</label>
          <input type="textfield" class="form-control" id="filepath" name="filepath" placeholder="./examples/vsltrans_gist.log">
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
        <button type="button" class="btn" onclick="this.form.filepath.value='./examples/vsltrans_gist.log';this.form.submit();">See example</button>
      </form>

    <?php elseif($error):?>
      <!-- An error occured -->
      <div class="alert alert-danger" role="alert"><?php echo $error ?></div>

    <?php else: ?>
      <!-- Everything is fine -->
      <div class="container">
        <h2>Sequence diagram</h2>
        <p>
          <a href="<?php echo $transactions_diagram_url ?>" target="_black" title="See this image at full size"><img alt="Sequence diagram, explained below" src="<?php echo $transactions_diagram_url ?>" class="img-responsive center-block" /></a>
          <p><strong>Note :</strong> this image will be destroyed in two minutes.</p>
        </p>
      </div>

      <div class="container">
        <h2>All transactions recorded</h2>
        <?php \Kint::dump( $transactions_list, "Transaction list" ); ?>
      </div>

      <div class="container">
        <h2>Simple representation</h2>
        <pre class="pre-scrollable"><?php print $transactions_string ?></pre>
      </div>

    <?php endif; ?>
  </div>
</body>
</html>
