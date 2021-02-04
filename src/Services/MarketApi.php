<?php

namespace App\Services;

use App\Entity\MarketItems;
use SteamApi\SteamApi;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityRepository;

class MarketApi extends SteamApi
{
    protected $manager;
    protected $count;
    protected $souvenir;
    protected $stattrack;
    protected $name;
    protected $condition;
    protected $itemId;
    public $prices;

    public function __construct(EntityManagerInterface $manager,$count = 1,$souvenir = false,$stattrack = false,$name = '',$condition = 0,$prices = [],$itemId = null)
    {
        $this->itemId = $itemId;
        $this->prices = $prices;
        $this->conditionArr = [
            0 => false,
            1 => "(Factory-New)",
            2 => "(Minimal-Wear)",
            3 => "(Field-Tested)",
            4 => "(Well-Worn)",
            5 => "(Battle-Scared)"
        ];
        $this->condition = $this->conditionArr[$condition];
        $this->name = $name;
        $this->souvenir = $souvenir;
        $this->stattrack = $stattrack;
        $this->msg = [
            'type' => null,
            'message' => []
        ];
        $this->count = $count;
        $this->manager = $manager;
        $this->item = new MarketItems;
        $this->repo = $this->manager->getRepository(MarketItems::class);
    }

    public function getOneByName()
    {
        return $this->repo->findOneBy(['name' => $this->name]);
    }

    public function setItemId($id = null)
    {
        $item = $this->getOneByName();
        if(!empty($item->getItemNameId()))
        {

            $this->itemId = $item->getItemNameId();

            return $this;
        }

        if($this->name == '')
        {
            return false;
        }

        $options = [
            'market_hash_name' => $this->name
        ];

        (is_null($id)) ? $this->itemId = $this->getItemNameId(730,$options) : $this->itemId = $id;

        $item->setItemNameId($this->itemId);
        $this->manager->persist($item);
        $this->manager->flush();

        return $this;
    }

    public function getInspectItemData()
    {

    }

    public function getData()
    {  
        return $this->repo->findBy([],['name' => 'DESC']);
    }

    public function saveData()
    {
        $success = 0;

        if(empty($this->name))
        {
            $this->msg['type'] = 'alert';
            $this->msg['message'][] .= 'Please specify item name !';
            return $this->msg;
        }

        if($this->stattrack && !$this->souvenir) $this->name = "StatTrak™ ".$this->name;
        if(!$this->stattrack && $this->souvenir) $this->name = "Souvenir ".$this->name;
        if($this->stattrack && $this->souvenir)
        {
            $this->msg['type'] = 'alert';
            $this->msg['message'][] .= 'There is no item in the store with stattrack and souvernir combined together';
            return $this->msg;
        }

        if($this->condition) $this->name .= " ".$this->condition;

        $options = [
            'start' => 0,
            'count' => $this->count,
            'query' => $this->name,
            'exact' => true
        ];
        $data = $this->searchItems(730,$options);
        if(isset($data['items']))
        {
            $data = $data['items'];
            foreach($data as $item)
            {
                try
                {
                    $itemObject = $this->item->setName($item['name'])->setPrice($item['price_text'])->setImg($item['image']);
                    if(!$this->manager->isOpen())
                    {
                        $this->manager = $this->manager->create(
                            $this->manager->getConnection(),
                            $this->manager->getConfiguration()
                        );
                    }
                    $this->manager->persist($itemObject);
                    $this->manager->flush();
                    $this->manager->clear(MarketItems::class);
                    $success++;
                }
                catch(UniqueConstraintViolationException $e)
                {
                    if(!$success)
                    {
                        $this->msg['type'] = 'alert';
                        $this->msg['message'][] .= "Item: ".$item['name']." already exsists in database";
                    }
                }
               
            }
            if($success)
            {
                $this->msg['type'] = 'success';
                unset($this->msg['message']);
                $this->msg['message'][] = $success." Item/Items has been added to list";
            }

        }
        else
        {
            $this->msg['type'] = 'alert';
            $this->msg['message'][] .= "No item found on the market ";
            return $this->msg;

        }
        
        return $this->msg;
    }

    public function deleteData()
    {
        if($item = $this->repo->findOneBy(['name' => $this->name]))
        {
            $this->manager->remove($item);
            $this->manager->flush();
            $this->msg['type'] = 'success';
            $this->msg['message'][] .= "Removed: ".$item->getName()." from list";
        }
        else
        {
            $this->msg['type'] = 'error';
            $this->msg['message'][] .= "Specified item was not found in database";
        }
        
        return $this->msg;
    }

    public function getItemSaleHistory()
    {
        $options = [
            'market_hash_name' => $this->name,
        ];

        $this->prices = $this->getSaleHistory(730,['market_hash_name' => $this->name]);

        return $this;
    }

    public function updateData($price = null,$name = null)
    {
        if($item = $this->repo->findOneBy(['name' => $this->name]))
        {
            if(!is_null($price)) $item->setPrice($price);
            if(!is_null($name)) $item->setName($name);

            $this->manager->persist($item);
            $this->manager->flush();

            return true;
        }

        return false;

    }

    public function sortPricesByDate()
    {
        if(empty($this->prices)) return false;

        $data = [];
        $year = null;
        $month = null;
        $date = null;

        foreach($this->prices as $item)
        {
            $saleDate = \explode("-",$item['sale_date']);

            if($item['sale_date'] != $date)
            {
                $date = $item['sale_date'];
                $saleYear = $saleDate[0];
                $saleMonth = $saleDate[1];
                $saleDay = $saleDate[2];

                if($year !== $saleYear)
                {
                    $year = $saleYear;
                    $data[$year] = [];
                }

                if($month !== $saleMonth)
                {
                    $month = $saleMonth;
                    $data[$year][$month] = [];
                }

                $item['sale_price'] = \number_format(\round($item['sale_price'],2),2);
                $data[$year][$month][] = $item;
            }
           
        }

        $this->prices = $data;

        return $this->prices;

    }

    public function skipMultipleRecordsInDay()
    {
        if(empty($this->prices)) return false;

        $data = [];
        $date = null;
        $avg = 0;
        $count = 0;
        $prevDate = null;

        foreach($this->prices as $item)
        {   
            if($item['sale_date'] != $date)
            {
                if($count)
                {
                    $item['sale_price'] = $avg/$count;
                }
                $item['sale_price'] = \number_format(\round($item['sale_price'],2),2);
                $data[] = $item;
                $date = $item['sale_date'];
                $avg = 0;
                $count = 0;
               
            }
            else
            {
                $count ++;
                $prevDate = $item['sale_date'];
                $avg += $item['sale_price'];
            }
        }

        return $this->prices = $data;
    }

    /**
     * Get the value of count
     */ 
    public function getCount()
    {
        return $this->count;
    }

    /**
     * Set the value of count
     *
     * @return  self
     */ 
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Set the value of stattrack
     *
     * @return  self
     */ 
    public function setStattrack($stattrack)
    {
        $this->stattrack = $stattrack;

        return $this;
    }

    /**
     * Set the value of souvenir
     *
     * @return  self
     */ 
    public function setSouvenir($souvenir)
    {
        $this->souvenir = $souvenir;

        return $this;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set the value of condition
     *
     * @return  self
     */ 
    public function setCondition($condition)
    {
        if($condition > 5) $condition = 5;
        if($condition < 0) $condition = 0;

        $this->condition = $this->conditionArr[$condition];

        return $this;
    }

    /**
     * Get the value of prices
     */ 
    public function getPrices()
    {
        return $this->prices;
    }
}