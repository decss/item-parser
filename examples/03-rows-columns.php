<?php
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Parser;

$sizes = json_decode(file_get_contents('data/psizes.json'), true);
$colors = json_decode(file_get_contents('data/pcolors.json'), true);
$csvPath = 'data/file.csv';

// 1. Init Parser and set CSV file path
$parser = new Parser($csvPath);

// 2.1. Set rows to skip
$parser->skipRows([0]);

// 2.2. Config columns (in any order)
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
$parser->textField('item_image2');
$parser->textField('item_image3');

// 2.3. Set columns ordering and skip some columns
$parser->fieldsOrder([
    0 => 'item_name',
    1 => 'item_sku',
    2 => 'item_price',
    3 => 'item_color',
    4 => 'item_size',
    // 5, 6 - skip
    7 => 'item_material',
    8 => 'item_desc',
    9 => 'item_link',
    10 => 'item_image1',
    11 => 'item_image2',
    12 => 'item_image3',
    // further will be skipped
]);

// 3. Run parse and get results
$result = $parser->parse();

// 4. Init Drawer
$drawer = new Drawer($parser);

// 4.1. Hide some rows
$drawer->hideRows([6, 7, 8]);


?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>Rows & Columns</h3>
            <hr>
            <p>
                You can manipulate by rows and columns in <b>Parser</b> and <b>Drawer</b>.
                Drawer manipulations will have no affect to Parser results.
            </p>

            <h4>Parser</h4>
            <p>
                To skip rows you should call Parser <kbd>skipRows()</kbd> method as shown in <b>2.1.</b> of this file
                source code.<br>
                Skipped rows will not be excluded from the result, but all of them will have <u>skip</u> field with
                <u>true</u> value.
            </p>
            <p>
                To skip columns you need <kbd>fieldsOrder()</kbd> method. Using it you can apply configured <b>Field</b>
                to any CSV column, as shown in <b>2.3.</b>
            </p>

            <h4>Drawer</h4>
            <p>
                To hide rows from a table use <kbd>hideRows()</kbd> as shown in <b>4.1.</b>
                Also you can use <kbd>hideValid()</kbd> and <kbd>hideInvalid()</kbd> to hide valid or invalid rows
                accordingly.
                All 3 methods takes array if row(line) numbers from starting from 0.
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