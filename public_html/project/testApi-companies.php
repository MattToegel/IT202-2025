<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
if (isset($_GET["keyword"])) {
    $data = ["function" => "SYMBOL_SEARCH", "keywords" => $_GET["keyword"], "datatype" => "json"];
    $endpoint = "https://alpha-vantage.p.rapidapi.com/query";
    $isRapidAPI = true;
    $rapidAPIHost = "alpha-vantage.p.rapidapi.com";
    $result = get($endpoint, "STOCK_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    //example of cached data to save the quotas, don't forget to comment out the get() if using the cached data for testing
    /* $result = ["status" => 200, "response" => {
        "bestMatches": [
            {
                "1. symbol": "TESTF", 
                "2. name": "Test Foreign Security",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            },
            {
                "1. symbol": "TESTJ", 
                "2. name": "Test Security J1",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            },
            {
                "1. symbol": "TESTM", 
                "2. name": "Demo Test Security",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            },
            {
                "1. symbol": "TESTT", 
                "2. name": "Test Security T",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            }
        ]
    }'];*/
    error_log("API Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
}
?>
<div class="container-fluid">
    <h1>Company Info</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <form>
        <div>
            <label>Keyword</label>
            <input name="keyword" />
            <input type="submit" value="Fetch Stock" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result)) : ?>
            <?php foreach ($result as $stock) : ?>
                <pre>
                    <?php var_export($stock);?>
                </pre>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");