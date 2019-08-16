<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Repository\Admin\SettingRepository;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils, SettingRepository $settingRepository): Response
    {
        $data = $settingRepository->findAll();
        $cats = $this->categorytree();


        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'error' => $error,
            'last_username' => $lastUsername,
            'data' => $data,

            'cats'=>$cats,

        ]);
    }

    /**
     * @Route("/loginerror", name="app_login_error")
     */
    public function loginerror(AuthenticationUtils $authenticationUtils, SettingRepository $settingRepository): Response
    {
        $cats = $this->categorytree();
        $data = $settingRepository->findAll();



        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $this->addFlash('error', ' BU ALANA ERİŞİM HAKKINIZ YOKTUR');

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'data' => $data,
            'error' => $error,
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
