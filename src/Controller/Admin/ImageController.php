<?php

namespace App\Controller\Admin;

use App\Entity\Admin\Image;
use App\Form\Admin\ImageType;
use App\Repository\Admin\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/image")
 */
class ImageController extends AbstractController
{
    /**
     * @Route("/", name="admin_image_index", methods="GET")
     */
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('admin/image/index.html.twig', ['images' => $imageRepository->findAll()]);
    }

    /**
     * @Route("/{pid}/new", name="admin_image_new", methods="GET|POST")
     */
    public function new(Request $request,$pid,ImageRepository $imageRepository): Response
    {
        $imagelist = $imageRepository->findBy(['product_id'=>$pid]);
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($request->files->get('imagename')) {

            $file = $request->files->get('imagename');
            $filename = $this->generateUniqueFileName().'.'.$file->guessExtension();
            try{
                $file->move(
                    $this->getParameter('images_directory'),
                    $filename

                );
            }catch (FileException $e){

            }
            $image->setImage($filename);
            $image->setProductId($pid);

            $em = $this->getDoctrine()->getManager();



            $em->persist($image);
            $em->flush();

            return $this->redirectToRoute('admin_image_new',array('pid'=> $pid));
        }

        return $this->render('admin/image/new.html.twig', [
            'image' => $image,
            'imagelist' => $imagelist,
            'pid' => $pid,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="admin_image_show", methods="GET")
     */
    public function show(Image $image): Response
    {
        return $this->render('admin/image/show.html.twig', ['image' => $image]);
    }

    /**
     * @Route("/{id}/edit", name="admin_image_edit", methods="GET|POST")
     */
    public function edit(Request $request, Image $image): Response
    {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('admin_image_index', ['id' => $image->getId()]);
        }

        return $this->render('admin/image/edit.html.twig', [
            'image' => $image,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{pid}", name="admin_image_del", methods="GET")
     */
    public function del(Request $request, Image $image,$pid): Response
    {

            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();


        return $this->redirectToRoute('admin_image_new',array('pid'=> $pid));
    }




    /**
     * @Route("/{id}", name="admin_image_delete", methods="DELETE")
     */
    public function delete(Request $request, Image $image): Response
    {
        if ($this->isCsrfTokenValid('delete'.$image->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($image);
            $em->flush();
        }

        return $this->redirectToRoute('admin_image_index');
    }
    /**
     * @return string
     */
    private function generateUniqueFileName(){
        return md5(uniqid());
    }
}
