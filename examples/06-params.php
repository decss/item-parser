<?php
require 'inc.head.php';


use ItemParser\Drawer;
use ItemParser\Parser;

$sizes = json_decode(file_get_contents('data/psizes.json'), true);
$colors = json_decode(file_get_contents('data/pcolors.json'), true);
$csvPath = 'data/file.csv';

// Get missings configs
$colorsMissing = $_POST['parseMissing']['item_color'];
$sizesMissing = $_POST['parseMissing']['item_size'];
if (!$sizesMissing) {
    $sizesMissing = [
        "5-6" => 0,
        "6-7" => 3,
        "7 (S)" => 4,
        "8 (S)" => 5,
        "10 (M)" => 7,
        "16 (XL)" => -1,
    ];
}

// 1. Init Parser and set CSV file path
$parser = new Parser($csvPath);

// 2.1. Set rows to skip
$parser->skipRows([0]);

// 2.2. Config columns (in any order)
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->paramField('item_color', [$colors, $colorsMissing]);
$parser->paramField('item_size', [$sizes, $sizesMissing])->required(true)->delimiters([';', '/']);
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

// 4.1. Hide some rows
$drawer->hideRows([0, 1, 2, 6, 7, 8]);

?>


<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-6">
            <h3>Missing params</h3>
            <hr>
            <p>
                Here you can see how parameters replacement works.
                If column was configured as Param Field - parser will extract values from cell
                and search it in param array, that was configured to that Fields (i.e. $colors, $sizes)
            </p>
            <p>
                Values are displayed in Drawer table as tags. Value can be found (white tag), not found (red tag), skipped (gray tag) or replaced (green tag) with existing param.
                It can be configured by passing missing array when field is configured (you can see it in <b>2.2.</b> of this file source code)
            </p>

            <p>
                Note that if Field is required and has no one valid values - Field will be invalid
            </p>

            <p>
                By default cell values separated by one of <kbd>;</kbd> or <kbd>,</kbd> chars. You can define your own delimiters by calling Field's <kbd>delimiters(string|array)</kbd> method:
            </p>
            <pre>$parser->paramField('name', [$params])->delimiters([';', '/']);</pre>
            <p>
                If you set more than one delimiter - the most common character will be used (delimiter frequency calculated for each cell separately).
                You can see how custom delimiter <kbd>/</kbd> works at <b>6</b> line
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