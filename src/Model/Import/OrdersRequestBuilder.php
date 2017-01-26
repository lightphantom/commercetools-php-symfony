<?php
/**
 * Created by PhpStorm.
 * User: ibrahimselim
 * Date: 23/01/17
 * Time: 14:14
 */

namespace Commercetools\Symfony\CtpBundle\Model\Import;

use Commercetools\Core\Client;
use Commercetools\Core\Model\Order\ImportOrder;
use Commercetools\Core\Model\Order\Order;
use Commercetools\Core\Request\Orders\OrderImportRequest;
use Commercetools\Core\Request\Orders\OrderQueryRequest;

class OrdersRequestBuilder extends AbstractRequestBuilder
{
    const ID ='id';
    const VERSION ='version';
    const NAME ='name';
    const VARIANT ='variant';
    const LINEITEMS ='lineItems';
    const TOTALPRICE ='totalPrice';
    const CURRENCYCODE ='currencyCode';
    const CENTAMOUNT ='centAmount';
    const BILLINGADDRESS ='billingAddress';
    const SHIPPINGADDRESS ='shippingAddress';
    const QUANTITY ='quantity';
    const PRICE ='price';
    const VALUE ='value';
    const PRODUCTID ='productId';
    const VARIANTID ='variantId';
    const ORDERSTATE ='orderState';
    const SHIPMENTSTATE ='shipmentState';
    const PAYMENTSTATE ='paymentState';

    private $client;
    private $orderDataObj;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->orderDataObj= new OrderData($client);
    }

    private function getOrdersByIdentifiedByColumn($orders, $identifiedByColumn)
    {
        $parts = explode('.', $identifiedByColumn);
        $ordersArr=[];
        foreach ($orders as $order) {
            switch ($parts[0]) {
                case self::ID:
                    $ordersArr[$order->toArray()[$identifiedByColumn]] = $order;
                    break;
            }
        }
        return $ordersArr;
    }
    private function getOrdersDataByIdentifiedByColumn($ordersData, $identifiedByColumn)
    {
        $ordersDataArr=[];
        $parts = explode('.', $identifiedByColumn);
        foreach ($ordersData as $orderData) {
            switch ($parts[0]) {
                case self::ID:
                    $ordersDataArr[$orderData[$identifiedByColumn]] = $orderData;
                    break;
            }
        }
        return $ordersDataArr;
    }
    /**
     * @param $ordersData
     * @param $identifiedByColumn
     * @return ClientRequestInterface[]|null
     */
    public function createRequest($ordersData, $identifiedByColumn)
    {
        $requests=[];
        $request = OrderQueryRequest::of()
            ->where(
                sprintf(
                    $this->getIdentifierQuery($identifiedByColumn),
                    $this->getIdentifierFromArray($identifiedByColumn, $ordersData)
                )
            )
            ->limit(500);

        $response = $request->executeWithClient($this->client);
        $orders = $request->mapFromResponse($response);

        $ordersArr=$this->getOrdersByIdentifiedByColumn($orders, $identifiedByColumn);
        $ordersDataArr=$this->getOrdersDataByIdentifiedByColumn($ordersData, $identifiedByColumn);
        /**
         * @var Order $order
         */
        foreach ($ordersDataArr as $key => $orderData) {
            if (isset($ordersArr[$key])) {
//                $order = $ordersArr[$key];
//                $request = $this->getUpdateRequest($orderData, $order);
//                if (!$request->hasActions()) {
//                    $request = null;
//                }
//                $requests []=$request;
            } else {
                $request  = $this->getCreateRequest($orderData);
                $requests []= $request;
            }
        }
        return $requests;
    }

//    private function getUpdateRequestsToChange($toChange)
//    {
//        $actions=[];
//        foreach ($toChange as $heading => $data) {
//            switch ($heading) {
//                case self::ORDERSTATE:
//                    $actions[$heading] = OrderChangeOrderStateAction::ofOrderState($data);
//                    break;
//                case self::SHIPMENTSTATE:
//                    $actions[$heading] = OrderChangeShipmentStateAction::ofShipmentState($data);
//                    break;
//                case self::PAYMENTSTATE:
//                    $actions[$heading] = OrderChangePaymentStateAction::ofPaymentState($data);
//                    break;
//            }
//        }
//        return $actions;
//    }
//    private function getUpdateRequest($orderDataArray, Order $order)
//    {
//        $orderDataArray= $this->orderDataObj->mapOrderFromData($orderDataArray);
//        $order = $order->toArray();
//
//        $toChange = $this->orderDataObj->getOrderItemsToChange($orderDataArray, $order);
//
//        $actions=[];
//        $actions = array_merge_recursive($actions, $this->getUpdateRequestsToChange($toChange));
//
//        $request = OrderUpdateRequest::ofIdAndVersion($order[self::ID], $order[self::VERSION]);
//        $request->setActions($actions);
//    //        print_r((string)$request->httpRequest()->getBody());
//        return $request;
//    }

    private function getCreateRequest($orderDataArray)
    {
        $orderDataArray= $this->orderDataObj->mapOrderFromData($orderDataArray);
        $orderDataobj= $this->orderDataObj->getOrderObjsFromArr($orderDataArray);
        $order = ImportOrder::fromArray($orderDataobj);
        $request = OrderImportRequest::ofImportOrder($order);
        return $request;
    }

    public function getIdentifierQuery($identifierName, $query = ' in (%s)')
    {
        $value = '';
        switch ($identifierName) {
            case self::ID:
                $value = $identifierName. $query;
                break;
        }
        return $value;
    }
    public function getIdentifierFromArray($identifierName, $rows)
    {
        $parts = explode('.', $identifierName);
        $value=[];
        foreach ($rows as $row) {
            switch ($parts[0]) {
                case self::ID:
                    $value [] = '"'.$row[$parts[0]].'"';
                    break;
            }
        }
        return implode(',', $value);
    }
}
