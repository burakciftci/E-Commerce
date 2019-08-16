<?php

namespace App\Controller;

use App\Entity\Admin\Messages;
use App\Entity\User;
use App\Form\Admin\MessagesType;
use App\Form\UserType;
use App\Repository\Admin\CategoryRepository;
use App\Repository\Admin\ImageRepository;
use App\Repository\Admin\ProductRepository;
use App\Repository\Admin\SettingRepository;
use App\Repository\UserRepository;
use http\Env\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(SettingRepository $settingRepository)
    {
        $data = $settingRepository->findAll();

        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM product WHERE status = 'True' ORDER BY  ID DESC  LIMIT 5";
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $sliders = $statement->fetchAll();

        $sqla = "SELECT * FROM product WHERE status = 'True' ORDER BY ID ASC  LIMIT 4 ";
        $statement = $em->getConnection()->prepare($sqla);
        $statement->execute();
        $products = $statement->fetchAll();

        $sqlb = "SELECT * FROM product WHERE sprice = '400' ORDER BY ID  LIMIT 4 ";
        $statement = $em->getConnection()->prepare($sqlb);
        $statement->execute();
        $product = $statement->fetchAll();

        $sqlc = "SELECT * FROM product WHERE amount = '3' ORDER BY ID ASC LIMIT 4 ";
        $statement = $em->getConnection()->prepare($sqlc);
        $statement->execute();
        $producta = $statement->fetchAll();

        $sqld = "SELECT * FROM product WHERE amount = '5' ORDER BY ID ASC LIMIT 4 ";
        $statement = $em->getConnection()->prepare($sqld);
        $statement->execute();
        $productb = $statement->fetchAll();



        $cats = $this->categorytree();
        $cats[0] = '<ul id="menu-v">';
       // print_r($cats);
        //die();
        return $this->render('home/index.html.twig', [
            'data'=>$data,
            'cats'=>$cats,
            'sliders'=>$sliders,
            'products'=>$products,
            'product'=>$product,
            'producta'=>$producta,
            'productb'=>$productb,
        ]);
    }
    /**
     * @Route("/category/{catid}",name="category_product", methods="GET")
     */
    public  function CategoryProducts($catid,CategoryRepository $categoryRepository){


        $data = $categoryRepository->findBy(
            ['id'=> $catid]
        );

        $em = $this->getDoctrine()->getManager();
        $sql = 'SELECT * FROM product WHERE status="True" AND category_id = :catid';
        $statement = $em->getConnection()->prepare($sql);
        $statement->bindValue('catid',$catid);
        $statement->execute();
        $products = $statement->fetchAll();
        //dump($result);
        //die();
        $cats = $this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        return $this->render('home/products.html.twig', [
            'data'=>$data,
            'products'=>$products,
            'cats'=>$cats,

        ]);
    }
    /**
     * @Route("/product/{id}",name="product_detail", methods="GET")
     */
    public  function ProductsDetail($id,ProductRepository $productRepository,ImageRepository $imageRepository){

        $data=$productRepository->findBy(
            ['id'=> $id]
        );

        $images=$imageRepository->findBy(
            ['product_id'=> $id]
        );



        $cats = $this->categorytree();
        $cats[0] = '<ul id="menu-v">';
        return $this->render('home/product_detail.html.twig', [

            'data'  => $data,
            'cats' => $cats,
            'images' => $images,

        ]);
    }



    /**
     * @Route("/hakkimizda", name="hakkimizda")
     */
    public function hakkimizda(SettingRepository $settingRepository)
    {
        $cats = $this->categorytree();
        $data = $settingRepository->findAll();
        return $this->render('home/hakkimizda.html.twig', [
            'data'=>$data,
            'cats'=>$cats,
        ]);
    }

    /**
     * @Route("/iletisim", name="iletisim",methods="GET|POST")
     */

    public function iletisim(SettingRepository $settingRepository, \Symfony\Component\HttpFoundation\Request $request)
    {
        $message = new Messages();
        $form = $this->createForm(MessagesType::class, $message);
        $form->handleRequest($request);

        $submittedToken = $request->request->get('token');

        if ($form->isSubmitted()) {
            if($this->isCsrfTokenValid('form-messeage',$submittedToken)) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($message);
                $em->flush();
                $this->addFlash('success', ' Mesajınız Başarıyla Gönderilmiştir');

                return $this->redirectToRoute('iletisim');
            }
        }

        $cats = $this->categorytree();
        $data = $settingRepository->findAll();
        return $this->render('home/iletisim.html.twig', [
            'data'=>$data,
            'cats'=>$cats,
            'message'=>$message
        ]);
    }

    /**
     * @Route("/newuser", name="new_user",methods="GET|POST")
     */
    public function newuser(\Symfony\Component\HttpFoundation\Request $request,UserRepository $userRepository): Response
    {
        $cats = $this->categorytree();
        $user=new User();
        $form=$this->createForm(UserType::class,$user);
        $form->handleRequest($request);

        $submittedToken=$request->request->get('token');
        if ($this->isCsrfTokenValid('user-form', $submittedToken)) {
            if ($form->isSubmitted() ) {
                $emaildata=$userRepository->findBy(
                    ['email'=>$user->getEmail()]
                );
                if($emaildata==null) {
                    $em = $this->getDoctrine()->getManager();
                    $user->setRoles("ROLE_USER");
                    $em->persist($user);
                    $em->flush();
                    $this->addFlash('success', ' Kayıdınız oluşturuldu');
                    return $this->redirectToRoute('app_login');
                }
                else{
                    $this->addFlash('error', $user->getEmail()."Bu email adresi zaten var.");
                    return $this->redirectToRoute('app_login');

                }
            }
        }
        return $this->render('home/newuser.html.twig',[
            'user'=>$user,
            'form'=>$form->createView(),
            'cats'=>$cats,
        ]);
    }


    public function categorytree($parent = 0, $user_tree_array = ''){
        if(!is_array($user_tree_array))
            $user_tree_array = array();
        $em = $this->getDoctrine()->getManager();
        $sql = "SELECT * FROM category WHERE status = 'True' AND parentid =".$parent;
        $statement = $em->getConnection()->prepare($sql);
        $statement->execute();
        $result = $statement->fetchAll();

        if(count($result)>0){
            $user_tree_array[] = "<ul>";
            foreach ($result as $row){
                $user_tree_array[] = "<li>  <a href='/category/".$row['id']."'>". $row['title']."</a>";
                $user_tree_array = $this->categorytree($row['id'],$user_tree_array);
            }
            $user_tree_array[] = "</li></ul>";

        }
        return $user_tree_array;

    }












}
