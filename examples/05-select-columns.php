<?php
require __DIR__ . '/../vendor/autoload.php';
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

if ($_POST['parseOrdering']) {
    $parser->fieldsOrder($_POST['parseOrdering']);
} else {
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
}

// 3. Run parse and get results
$result = $parser->parse();

// 4. Init Drawer with options
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_sku' => ['title' => 'Prod. SKU'],
    'item_price' => ['title' => 'Price'],
    'item_size' => ['title' => 'Sizes'],
    'item_color' => ['title' => 'Colors'],
    'item_desc' => ['title' => 'Description', 'display' => 'text'],
    'item_link' => ['display' => 'link'],
    'item_image1' => ['title' => 'First Image', 'display' => 'image'],
    'item_image2' => ['title' => 'Second Image', 'display' => 'image'],
    'item_image3' => ['title' => 'Third Image', 'display' => 'image'],
]);


?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>Select columns</h3>
            <hr>
            <p>
                To make available manual column selection 3 steps are required: <br>
                1. Wrap table with form<br>
                2. Draw table head with column <i>selects</i> <kbd>echo $drawer->head('select');</kbd><br>
                3. Set new fields order by calling <kbd>$parser->fieldsOrder($_POST['parseOrdering']);</kbd>
            </p>

            <p>
                By default names of select elements is <b>parseOrdering</b>. If you need to set another name, you can do
                it by calling
                <kbd>$drawer->setOrderInputName($name)</kbd>
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
    <table class="parse-table">
        <thead>
        <?php echo $drawer->head('select') ?>
        </thead>
        <tbody>
        <?php echo $drawer->body() ?>
        </tbody>
    </table>
    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse"><br>
</form>


</body>
</html>