<?php
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Parser;

$sizes = json_decode(file_get_contents('data/psizes.json'), true);
$colors = json_decode(file_get_contents('data/pcolors.json'), true);
$csvPath = 'data/file.csv';

// 1. Init Parser and set CSV file path
$parser = new Parser($csvPath);

// 2. Config columns
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->paramField('item_color', [$colors]);
$parser->paramField('item_size', [$sizes])->required(true);
$parser->textField('item_size_text');
$parser->textField('item_collection');
$parser->textField('item_material');
$parser->textField('item_desc')->required();
$parser->textField('item_link');
$parser->textField('item_image1');

// 3. Run parse and get results
$result = $parser->parse();

// 4. Init Drawer
$drawer = new Drawer($parser);


?>

<div class="container-fluid">
<div class="row mb-4">
    <div class="col-6">
        <h3>Basic usage</h3>
        <hr>

        <h5>1. Set CSV file or content: </h5>
        <pre><code>// Set filepath
$parser = new Parser('file.csv');
// or
$parser = new Parser;
$parser->setCsvPath('file.csv');
// or Set CSV content
$parser = new Parser;
$content = file_get_contents('file.csv');
$parser->setCsvContent($content);</code></pre>

        <h4>2. Configure Fields</h4>
        <p>
            Each <b>Field</b> should have it's own uniq name. Field name is used as name of
            associated inputs and value of <b>"name"</b> property of row's <b>"fields"</b> items (Parser results).
        </p>
        <p>
            There are 2 field types: <b>text</b> for text data like name, price, url, description and <b>param</b>
            for parameters that has their uniq id and value. See <b>2.</b> of this file source code.<br>
            To add text field use <kbd>textField('field_name')</kbd> method<br>
            To add param field use <kbd>paramField('field_name', [$params, $missings])</kbd>, where: <br>
            <b>$params</b> is array of parameters, associated with field,<br>
            <b>$missings</b> is optional array of missing parameters where replace or skip for missing param can be
            defined
        </p>
        <p>
            Field can be set as required using <kbd>required()</kbd> method. Required field id invalid if it hasn't any
            value or some of field values (tags) has an error (for param fields)
        </p>

        <h4>3. Parsing</h4>
        <p>
            To get Parse results just do this: <kbd>$result = $parser->parse();</kbd> or
            <kbd>$result = $parser->result();</kbd> after.
        </p>
    </div>

    <div class="col-6">
        <h3>Parser results</h3>
        <?php dump($result) ?>
    </div>
</div>
</div>


<h2>Drawer view</h2>
<table class="parse-table">
    <thead>
    <?php echo $drawer->head() ?>
    </thead>
    <tbody>
    <?php echo $drawer->body() ?>
    </tbody>
</table>


</body>
</html>