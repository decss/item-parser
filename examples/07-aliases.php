<?php
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Parser;

$colors = [
    // ['id' => 1, 'value' => 'Black',],
    // ['id' => 2, 'value' => 'Blue',],
    // ['id' => 3, 'value' => 'Grey',],
    // ['id' => 4, 'value' => 'Orange',],
    // ['id' => 5, 'value' => 'Red',],
    [
        'id' => 5,
        'value' => 'Gold',
        'alias' => ['Golden', 'Golden-ORANGE']
    ],
];
$csvPath = 'data/file.csv';

// Get missings configs
$colorsMissing = $_POST['parseMissing']['item_color'];

// 1. Init Parser and set CSV file path
$parser = new Parser($csvPath);

// 2.1. Set rows to skip
$parser->skipRows([0]);

// 2.2. Config columns (in any order)
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->paramField('item_color', [$colors, $colorsMissing]);

// 2.3. Set columns ordering and skip some columns
$parser->fieldsOrder([
    0 => 'item_name',
    1 => 'item_sku',
    2 => 'item_price',
    3 => 'item_color',
    // further will be skipped
]);

// 3. Run parse and get results
$result = $parser->parse();

// 4. Init Drawer with options
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_sku' => ['title' => 'Prod. SKU'],
    'item_price' => ['title' => 'Price'],
    'item_size' => ['title' => 'Sizes'],
    'item_color' => ['title' => 'Colors'],
]);

// 4.1. Hide some rows and set crop lengths
$drawer->hideRows([6, 7, 8, 9, 10]);
$drawer->setTextLen(10);

?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>Param aliases</h3>
            <hr>
            In this example we use aliases to find values (colors) that are slightly different but but
            logically the same.<br>
            Lines 5 and 6 has <b>Golden</b> and <b>Golden-orange</b> cell values, but gold color param was formed with <b>alias</b> field:
            <pre>[
    'id' => 5,
    'value' => 'Gold',
    'alias' => ['Golden', 'Golden-ORANGE']
],</pre>
            <p>
                So 'gold', 'golden' and 'golden-orange' values will be considered as <b>Gold</b> with id = 5.<br>
                Parameter values search are case-insensitive
            </p>

        </div>

        <div class="col-6">
            <h3>Parser results</h3>
            <?php dump($result) ?>
            <b>Post array:</b>
            <?php dump($_POST) ?>
        </div>
    </div>
</div>


<h2>Drawer view</h2>
<form action="" method="POST">
    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse"><br>
    Missing params and display options:
    <div class="missing-tables">
        <?php echo $drawer->missing() ?>
    </div>

    <table class="parse-table">
        <thead>
        <?php echo $drawer->head() ?>
        </thead>
        <tbody>
        <?php echo $drawer->body() ?>
        </tbody>
    </table>
    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse"><br>
</form>


</body>
</html>