<?php

namespace App\Controller\Userpanel;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;


use http\Env\Request;
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/userpanel")
 */
class UserpanelController extends AbstractController
{
    /**
     * @Route("/", name="userpanel")
     */
    public function index()
    {
        $cats = $this->categorytree();
        return $this->render('userpanel/show.html.twig',[
            'cats'=>$cats,
        ]);
    }

    /**
     * @Route("/edit", name="userpanel_edit",methods="GET|POST")
     */
    public function edit(\Symfony\Component\HttpFoundation\Request $request): Response
    {
        $cats = $this->categorytree();
        $usersession = $this->getUser();
        $user = $this->getDoctrine()->getRepository(User::class)->find($usersession->getid());


        if ($request->isMethod('POST')) {
            $submittedToken = $request->request->get('token');
            if ($this->isCsrfTokenValid('user-form', $submittedToken)) {
                $user->setName($request->request->get("name"));
                $user->setPassword($request->request->get("password"));
                $user->setAddress($request->request->get("address"));
                $user->setCity($request->request->get("city"));
                $user->setPhone($request->request->get("phone"));
                $this->getDoctrine()->getManager()->flush();
                $this->addFlash('success', 'Bilgileriniz Güncellendi');
                return $this->redirectToRoute('userpanel_show');

            }
        }
        return $this->render('userpanel/edit.html.twig',[
            'user'=>$user,
            'cats'=>$cats,
        ]);


    }

    /**
     * @Route("/show", name="userpanel_show",methods="GET")
     */
    public function show()
    {
        $cats = $this->categorytree();
        return $this->render('userpanel/show.html.twig',[
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
