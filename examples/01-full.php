<?php
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Parser;

$sizes = json_decode(file_get_contents('data/psizes.json'), true);
$colors = json_decode(file_get_contents('data/pcolors.json'), true);
$csvPath = 'data/file.csv';

$colorsMissing = $_POST['parseMissing']['item_color'];
$sizesMissing = $_POST['parseMissing']['item_size'];
if (!$sizesMissing) {
    $sizesMissing = [
        "5-6" => 0,
        "6-7" => 0,
        "7 (S)" => 4,
        "8 (S)" => 5,
        "10 (M)" => 7,
        "16 (XL)" => -1,
    ];
}

// 1. Init Parser and set CSV file path
$parser = new Parser($csvPath);

// 1.1. Get ParseCsv object and configure it directly
$parser->getCsvObj()->delimiter = ';,';

// 2.1. Set rows to skip
$parser->skipRows([0]);

// 2.2. Config columns
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->paramField('item_color', [$colors, $colorsMissing]);
$parser->paramField('item_size', [$sizes, $sizesMissing])->required(true)->delimiters([';', '/']);;
$parser->textField('item_material');
$parser->textField('item_desc')->required();
$parser->textField('item_collection');
$parser->textField('item_link');
$parser->textField('item_image1');
$parser->textField('item_image2');
$parser->textField('item_image3');

// 2.3. Set columns order
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

// 4.1. Init Drawer
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_sku' => ['title' => 'Product SKU'],
    'item_price' => ['title' => 'Price'],
    'item_size' => ['title' => 'Sizes'],
    'item_color' => ['title' => 'Colors'],
    'item_desc' => ['title' => 'Description', 'display' => 'text'],
    'item_link' => ['display' => 'link'],
    'item_image1' => ['display' => 'image'],
    'item_image2' => ['display' => 'image'],
    'item_image3' => ['display' => 'image'],
]);

// 4.2. Set hidden rows
if ($_POST['parseHide'] == 'valid') {
    $drawer->hideValid();
} elseif ($_POST['parseHide'] == 'invalid') {
    $drawer->hideInvalid();
} elseif (!$_POST['parseHide'] || $_POST['parseHide'] == 'custom') {
    $drawer->hideRows([0, 6, 7, 8]);
}

// Change max text length for ['display' => 'text'] cells
$drawer->setTextLen(55);

// Disable text crop fro skipped columns
// $drawer->cropSkipped(false);


?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>All features</h3>
            <hr>
            <p>Showcase of full-featured <b>Parser</b> and <b>Drawer</b> class</p>
            <p>See course for better understanding</p>
        </div>

        <div class="col-6">
            <h3>Parser results</h3>
            <?php dump($result) ?>
        </div>
    </div>
</div>


<h2>Drawer view</h2>
<form action="" method="post">
    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse"><br>

    Missing params and display options:
    <div class="missing-tables">
        <?php echo $drawer->missing() ?>

        <div style="display: inline-block">
            <label class="mb-0"><input type="radio" name="parseHide"
                                       value="all" <?php echo($_POST['parseHide'] == 'all' ? 'checked' : '') ?>> Show
                all rows</label><br>
            <label class="mb-0"><input type="radio" name="parseHide"
                                       value="valid" <?php echo($_POST['parseHide'] == 'valid' ? 'checked' : '') ?>>
                Hide valid</label><br>
            <label class="mb-0"><input type="radio" name="parseHide"
                                       value="invalid" <?php echo($_POST['parseHide'] == 'invalid' ? 'checked' : '') ?>>
                Hide invalid</label><br>
            <label class="mb-0"><input type="radio" name="parseHide"
                                       value="custom" <?php echo(!$_POST['parseHide'] || $_POST['parseHide'] == 'custom' ? 'checked' : '') ?>>
                Hide custom (1, 7-9)</label><br>
        </div>
    </div>

    Results:
    <table class="parse-table">
        <thead>
        <?php echo $drawer->head('select') ?>
        </thead>
        <tbody>
        <?php echo $drawer->body() ?>
        </tbody>
    </table>

    <input type="submit" class="btn btn-success btn-lg btn-block" value="Apply and parse">
</form>


</body>
</html>