# ItemParser
ItemParser is a simple PHP class for parsing Products and other records 
with their parameters (like colors, sizes etc) from CSV, present results as array 
or display it as html table 
<p align="center"><img src="https://repository-images.githubusercontent.com/304974704/6d714400-1936-11eb-80cc-be56e1450cd9"></p>

**See examples for description**


## Features
**Parser** features:
* Parse data from csv to array
* Display parse results in table view
* Parse parameters like `size`, `color`, `material`, `category`, etc from cells like `S; M; L; XL` to array of `[id => 1, value => "S"]` items
* Detect missing parameters and give an ability to replace or ignore it
* Configure each column type and order or skip it
* Search parameters by value or aliases
* Skip specified rows or columns


**Drawer** features:
* Select, change or skip each column manually
* Display parameters as tags
* Mark tags as `ignored`, `replaced` or `not found`
* Mark cell as `valid` or `invalid`
* Shorten links and image urls
* Shorten long text
* Hide valid, invalid or custom rows


## Installation
Using Composer run the following on the command line:
```
composer require decss/item-parser
```
Include Composer's autoloader file in your PHP script:
```php
require_once __DIR__ . '/vendor/autoload.php';
```

#### Without composer
Not recommended. To use ItemParser, you have to add a `require 'itemparser.lib.php';` line. 
Note that you will need to require all ItemParser dependencies like `ParseCsv`. 


## Parser result
 Paeser result is an array of rows (lines). Each row matches the corresponding line in the CSV and generally looks as follows:
```php
0 => [
    "row"    => 1,          // line number in CSV
    "valid"  => true,       // true if all row's Fields is valid, false if any is invalid
    "skip"   => false,      // true only if you skip this row by special method
    "fields" => []          // array of row fields (cells)
] 
```

Skipped rows can be both valid (`"valid" => true`) or invalid (`"valid" => false`) and vice versa.

As mentioned above, `"fields"` is an array of Field items. Each Field can be different depending on its type, config and content.
All row fields will be presented in result, even if Field was not parsed or was skipped or invalid - there is no matter. 

#### Empty field
This is an example of skipped or not configured Field:
```php
14 => [
    "text"  => "cell text", // Original CSV cell text
    "name"  => null,        // Field name from Parser Fields config
    "type"  => null         // Field type
]
```

#### Text field
So there is 2 Field types: `text` and `param`. Here is example of configured `text` Field:
```php
1 => [
    "text"  => "V_3J689910",
    "name"  => "item_sku",
    "type"  => "text",
    "valid" => true,        // true if Field is not required or required and have valid "value"
    "value" => "V_3J689910" // Unlike "text", "value" is the processed value of a cell.
]
```
`"value"` - is what you should to use instead of `"text"`

#### Param field and values
Next is "param" Field:
```php
3 => [
    "text" => "Black; Not a color; Grey; ",
    "name" => "item_color",
    "type" => "param",
    "valid" => false,
    "value" => [
        0 => [
            "valid" => true,    // true if param was found in Field params
            "skip" => false,    // true if this value was skipped in Field missings config
            "replace" => false, // true if this value was replaced in Field missings config
            "id" => 1,          // Param ID, if it's value was found by in Field params
            "value" => "Black", // Param or Replaced param value
            "text" => "Black"   // Param text extracted from cell text value
        ],
        1 => [
            "valid" => false,
            "skip" => false,
            "replace" => false,
            "id" => null,
            "value" => null,
            "text" => "Not a color"
        ],
        2 => ["valid" => true, "skip" => false, "replace" => false, "id" => 3, "value" => "Grey", "text" => "Grey"]
    ]
]
```
So you can see that `"value"` of `param` Field is an array. Here is example of both found `[0,2]` and not found `[1]` colors. 
If there is 2 or more identical colors (ie `"Black; Red; Black"`) all fo them will be valid but duplicates will be skipped. 


## Usage
#### Parser usage
```php
use ItemParser\Parser;

// 1. Init Parser and set CSV file path
$csvPath = 'file.csv';
$parser = new Parser($csvPath);

// 2. Config columns
$parser->textField('item_name')->required();
$parser->textField('item_sku')->required();
$parser->textField('item_price')->required();
$parser->textField('item_link');
$parser->textField('item_image1');
$parser->textField('item_image2');
// 2.1 Config param column
// Param array
$colors = [
    ['id' => 1, 'value' => 'Red'],
    ['id' => 2, 'value' => 'Green'],
    ['id' => 3, 'value' => 'Blue'],
    ['id' => 4, 'value' => 'Gold', 'alias' => ['Gold sand', 'Golden-Orange']],
];
// Param Missing - skip or replace colors, that was not found in $colors
$colorsMissing = [
    'Orange' => -1, //  Skip this color
    'Golden' => 4,  //  Replace "Golden" to "Gold" (id = 4)
];
$parser->paramField('item_color', [$colors, $colorsMissing])->required();

// 3. Run parse and get results
$result = $parser->parse();
```

#### Drawer usage
```php
use ItemParser\Drawer;

// 1. Init Parser and set CSV file path
$drawer = new Drawer($parser, [
    'item_name' => ['title' => 'Product Name'],
    'item_link' => ['display' => 'link'],
    'item_image1' => ['display' => 'image'],
]);

// Display results
echo '<table class="parse-table">'
    . '<thead>' . $drawer->head() . '</thead>'
    . '<tbody>' . $drawer->body() . '</tbody>'
    . '</table>';
```


## Detailed usage
#### Create Parser and set content
```php
// Set CSV file path
$parser = new Parser('file.csv');
// or
$parser = new Parser;
$parser->setCsvPath('file.csv');

// Set SCV content
$parser = new Parser;
$content = file_get_contents('file.csv');
$parser->setCsvContent($content);
```

#### Configure columns
```php
// Add text field
$parser->textField('column_name');
// Add required text field
$parser->textField('column2_name')->required();

// Add param field
$parser->paramField('item_size', [$sizes]);
// Add required param field with missing colors
$parser->paramField('item_color', [$colors, $colorsMissing])->required();
```
See examples to get how arguments like `$colors` and `$colorsMissing` work

#### Configure parser options
```php
// Skip first 2 rows
$parser->skipRows([0,1]);

// Skip columns and set order
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
```
#### Parsing and results
```php
// Do parsing and get results
$result = $parser->parse();

// Get results after parsing
$result = $parser->result();
```

#### Use Drawer
```php
// Create Drawer and config it
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

// Hide valid rows
$drawer->hideValid();
// Hide invalid rows
$drawer->hideInvalid();
// Hide custom rows
drawer->hideRows([0, 6, 7, 8]);

// Display missing selects
echo $drawer->missing();

// Display table column names
echo $drawer->head();
// Display table column names with Field selects
echo $drawer->head('select');

// Display table rows
echo $drawer->body();
```


## Changelog
#### ItemParser 1.0.0
- Date: 2020-10-26
- First release

#### ItemParser 1.0.1
- Date: 2020-10-26
- Breaking changes: none
- New features: none
- Bug fixes: Examples (performance page) 
- Code quality: Typos


## Credits

* ItemParser is based on [ParseCsv][] class.

[ParseCsv]: https://github.com/parsecsv/parsecsv-for-php