<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * User controller.
 *
 * @Route("")
 */
class UserController extends Controller
{
    /**
     * @Route("/login", name="security_login")
     * @param Request $request
     * @param AuthenticationUtils $authUtils
     * @return \Symfony\Component\HttpFoundation\Response
     * @internal param Request $request
     */
    public function loginAction(Request $request, AuthenticationUtils $authUtils)
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('user/login.html.twig', array(
            'last_username' => $lastUsername,
            'error'         => $error
        ));
    }


    /**
     * @Route("/register", name="security_register")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        $user = new User();

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted()) {
            $usernameExist = $userRole = $this->getDoctrine()
                ->getRepository(User::class)->findOneBy(["username"=>$user->getUsername()]);
            if($usernameExist) {
                $this->addFlash("error", "User exist");
//                return $this->render('user:register.html.twig', [
                return $this->render('user/register.html.twig', [
//                    'error' => "User exist"
                    'error' => ""
                ]);
            }
            else {
                $password = $this->get("security.password_encoder")->encodePassword($user, $user->getPassword());

//                $userRole = $this->getDoctrine()->getRepository(Role::class)->findOneBy(["name"=>"ROLE_USER"]);
//                $user->addRole($userRole);
                $user->setPassword($password);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();


            }

            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            return $this->redirectToRoute('homepage');
        }

        return $this->render('user/register.html.twig', ['error' => ""]);
//        return $this->render('user/register.html.twig', array('form' => $form->createView()));
//        return $this->render(':user:register.html.twig', array('form' => $form->createView()));
    }






    /**
     * @Route("/logout", name="security_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute("security_login");
    }
}
