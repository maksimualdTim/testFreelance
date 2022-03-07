<?php
require_once 'auth.php';

use Parser\Db;

const SPREAD_SHEET_ID = '10En6qNTpYNeY_YFTWJ_3txXzvmOA7UxSCrKfKCFfaRw';
const SHEET_RANGE = 'MA!A3:N109';

// Get the API client and construct the service object.
$client = getClient();
$sheetService = new Google_Service_Sheets($client);
$driveService = new Google_Service_Drive($client);

$database = Db::init();

$response = $sheetService->spreadsheets_values->get(SPREAD_SHEET_ID, SHEET_RANGE);

$sheetValues = $response->getValues();
$months = array_shift($sheetValues);
array_shift($months);

$currentCategoryID = 0;
$needUpdate = false;

foreach ($sheetValues as $sheetValue) {
    if (count($sheetValue) === 0) {
        continue;
    }
    if (count($sheetValue) === 1) {
        $category_name = $sheetValue[0];
        $category = $database->getCategory($category_name);

        if ($category) {
            $currentCategoryID = $category['id'];
            continue;
        }
        $database->saveCategory($category_name);
        $currentCategoryID = $database->getLastRecordID('categories');
        continue;
    }
    if ($sheetValue[0] !== '') {
        $product_name = array_shift($sheetValue);
        $category_id = $currentCategoryID;

        $toStore = [
            'category_id' => $category_id,
            'product_name' => $product_name,
        ];

        $budgets = $database->getBudget($product_name, $category_id);

        if (!$budgets) {
            foreach ($sheetValue as $month => $money) {
                $toStore[$months[$month]] = $money;
            }
            $database->saveBudget($toStore);
            continue;
        }

        foreach ($sheetValue as $month => $money) {

            if ($budgets[$month] != $money) {
                $toStore[$months[$month]] = $money;
                $changes[$currentCategoryID][] = $sheetValue;
                $needUpdate = true;
            }

        }
        if($needUpdate){
            $database->updateBudgets($toStore);
            $needUpdate = false;
        }

    }
}