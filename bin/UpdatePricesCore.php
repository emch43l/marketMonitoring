<?php

namespace UpdatePrices;

require __DIR__.'/../vendor/autoload.php';

use SteamApi\SteamApi;




define('WHEN_LOOP', 14400); //14400
define('SLEEP_TIME', 60);


set_time_limit(0);


$last_loop = 0;

$api = new SteamApi;





function connection()
{
    $db = new \mysqli("localhost","root","","app");

    if($db->errno)
    {
        die();
    }

    return $db;
}

function getItems()
{
    $items = [];

    $db = connection();

    if($stmt = $db->prepare("SELECT * FROM market_items"))
    {
        $stmt->bind_result($id,$name,$price,$img);
        if($stmt->execute())
        {
            while($stmt->fetch())
            {
                $items[] = ["id" => $id, "name" => $name, "price" => $price, "img" => $img];
            }
            $db->close();
            return $items;
        }
    }
    $db->close();
    return false;
}

function updatePrice($name,$price)
{
    $db = connection();

    if($stmt = $db->prepare("UPDATE market_items SET price=? WHERE name=?"))
    {
        $stmt->bind_param('ss',$price,$name);
        $stmt->execute();
        $stmt->store_result();
        $db->close();
        return $stmt->affected_rows;
    }
    $db->close();
    return false;

}

function updateMain($result_item,$item)
{
    if($result_item[0]['name'] == $item['name'])
    {
        if($result_item[0]['price_text'] != $item['price'])
        {
            echo "[".date("F j, Y, g:i a")."] Updating price for: ".$item['name']." from: ".$item['price']." to: ".$result_item[0]['price_text']."\n";
            updatePrice($item['name'],$result_item[0]['price_text']);
        }

        echo "Nothing to update, skipping...\n";
        
    }
}



//main loop
do
{
    if(time() > WHEN_LOOP + $last_loop)
    {
        //loop all items and update their prices
        //sleep because volvo sucks !!!!111
        if($items = getItems())
        {
            foreach($items as $item)
            {
                $options = [
                    'start' => 0,
                    'count' => 1,
                    'query' => $item['name'],
                    'exact' => true
                ];
                $result_item = $api->searchItems(730,$options);
                if($result_item)
                {
                    $result_item = $result_item['items'];
                    updateMain($result_item,$item);
                }
                else
                {
                    $count = 0;
                    $sleep = 0;
                    do
                    {
                        $sleep += 20;
                        sleep($sleep);
                        $count ++;
                        echo "error getting item data from steam servers, retrying... (".$count.")\n";
                        
                    }
                    while(!$result_item = $api->searchItems(730,$options) and $count < 5);
                    
                    if($count == 5 and !$result_item)
                    {
                        echo "maximum numer of retries has exceeded, skipping item ".$item['name']."\n";
                    }
                    else
                    {
                        updateMain($result_item['items'],$item);
                    }  
                }
                sleep(rand(2,10));
            }

        }

        $last_loop = time();
        
    }
    $seconds = WHEN_LOOP + $last_loop - time();
    $minutes = $seconds / 60;
    $hours = $seconds / 3600;
    echo "\033[2J\033[;H";
    echo "GOING SLEEP FOR: ".ceil($minutes)." MINUTES\r";
    sleep(SLEEP_TIME);
}
while(TRUE);







