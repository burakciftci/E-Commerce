<?php

namespace App\Controller\Admin;

use App\Entity\Orders;
use App\Repository\OrderDetailRepository;
use App\Repository\OrdersRepository;
use http\Env\Request;
use http\Env\Response;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;




/**
 * @Route("/admin")
 */

class AdminController extends AbstractController
{
    /**
     * @Route("/", name="admin")
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    /**
     * @Route("/orders/{slug}", name="admin_orders_index")
     */
    public function orders($slug,OrdersRepository $ordersRepository)
    {
        $orders = $ordersRepository->findBy(['status'=> $slug]);

        return $this->render('admin/orders/index.html.twig', [
        'orders' => $orders,
    ]);
    }

    /**
     * @Route("/orders/show/{id}", name="admin_orders_show",methods="GET")
     */
    public function show($id,Orders $orders,OrderDetailRepository $orderDetailRepository): \Symfony\Component\HttpFoundation\Response
    {
        $orderdetail = $orderDetailRepository->findBy(['orderid'=> $id]);
        return $this->render('admin/orders/show.html.twig', [
            'order' => $orders,
            'orderdetail'=> $orderdetail,
        ]);



    }

    /**
     * @Route("/order/{id}/update", name="admin_orders_update",methods="POST")
     */
    public function order_update($id,Orders $orders,\Symfony\Component\HttpFoundation\Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $em=$this->getDoctrine()->getManager();
        $sql="UPDATE orders SET shipinfo=:shipinfo,note=:note,status=:status WHERE id=:id";
        $statement=$em->getConnection()->prepare($sql);
        $statement->bindValue('shipinfo',$request->request->get('shipinfo'));
        $statement->bindValue('note',$request->request->get('note'));
        $statement->bindValue('status',$request->request->get('status'));
        $statement->bindValue('id',$id);
        $statement->execute();

        $this->addFlash('success','Sipariş bilgileri güncellendi!');



        return $this->redirectToRoute('admin_orders_show',array('id'=>$id));
    }




}
