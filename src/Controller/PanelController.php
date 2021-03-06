<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Services\MarketApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class PanelController extends AbstractController
{

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/panel", name="panel")
     */

    public function panel(MarketApi $api, Request $request)
    {
        if(is_null($message = $request->query->get('message')))
        {
            $type = false;
            $msg[0] = '';
        }
        else
        {
            (isset($message['type'])) ? $type = $message['type'] : $type = false;
            (isset($message['message']) && is_array($message['message'])) ? $msg = $message['message'] : $msg[0] = '';
        }

        return $this->render('panel/items/items.html.twig', ['data' => $api->getData(), 'error' => $type , 'message' => $msg[0]]);
    }

    /**
     * @Route("/panel/add", name="add_record", methods="GET")
     */

    public function addRecord(MarketApi $api, Request $request)
    {    
        $sttr = ($request->query->get('stattrack') && $request->query->get('stattrack') == 'on') ? true : false;
        $souve = ($request->query->get('souvenir') && $request->query->get('souvenir') == 'on') ? true : false;
        $conditionNum = ($condition = $request->query->get('condition')) ? $condition : 0;
        if($count = $request->query->get('count'))
        {
            if($count > 10) $count = 10;
            if($count < 1) $count = 1;
            $api->setCount($count);
        } 
        $name = $request->query->get('name');
        $msg = $api->setName($name)->setCondition($conditionNum)->setStattrack($sttr)->setSouvenir($souve)->saveData();

        return $this->redirectToRoute('panel', ['message' => $msg]);
    }

    /**
     * @Route("/panel/delete/{id}", name="remove_record", methods="GET")
     */

    public function deleteData(MarketApi $api, $id)
    {
        $msg = $api->setName($id)->deleteData();
        return $this->redirectToRoute('panel', ['message' => $msg]);
    }

    /**
     * @Route("/details", name="show_details", methods="GET")
     */

    public function getDetails(MarketApi $api, Request $request)
    {

        $item_data = false;
        $msg = ['type' => '', 'message' => []];

        if(!$name = $request->query->get('name'))
        {
            $msg['type'] = 'error';
            $msg['message'][] = 'Please specify item name';

            return $this->redirectToRoute('panel', ['message' => $msg]);
        }
        if($data = $api->setName($name)->getItemSaleHistory()->skipMultipleRecordsInDay())
        {
            $item = $api->getOneByName();
            $item_id = $api->setItemId();
            
            return $this->render('panel/details/details.html.twig',['error' => false, 'sale_history' => $data, 'item' => $item, 'item_data' => $item_id]);
        }

        $msg['type'] = 'error';
        $msg['message'][] = 'Error getting data from steam servers due to number of requests. Please try again later.';

        return $this->redirectToRoute('panel');  
        
    }

    /**
     * @Route("/update/{name}/{price}", name="update", methods="GET")
     */

    public function updatePrices(MarketApi $api, $name, $price)
    {
        $api->setName($name)->updateData('$'.$price);

        return $this->redirectToRoute('panel');
    }
}