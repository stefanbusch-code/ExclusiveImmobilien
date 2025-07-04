<?php

namespace App\Controller;



use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Totp\TotpAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode;






class UserSecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route(path: '/dashboard/2fa', name: 'app_dashboard_2fa_settings')]
    #[IsGranted('ROLE_USER')]
    public function twoFactorSettings(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if($request->isMethod('POST'))
        {
            $provider = $request->request->get('2fa_provider');

            if(in_array($provider, ['email', 'totp', 'none'], true))
            {
                $user->setPreferred2faProvider($provider === 'none' ? null :$provider);
                $entityManager->flush();
                $this->addFlash('success', '2FA-Einstellungen wurden aktualisiert');
                return  $this->redirectToRoute('app_dashboard_2fa_settings');
            }
        }
        return $this->render('security/2fa_settings.html.twig', [
            'isTotpEnabled' => $user->isTotpAuthenticationEnabled(),
            'currentProvider' => $user->getPreferred2faProvider(),
        ]);
    }

    #[Route(path: '/authenticate/2fa/enable', name: 'app_authenticate_2fa_enable')]
    #[IsGranted('ROLE_USER')]
    public function enable2fa(TotpAuthenticatorInterface $totpAuthenticator, EntityManagerInterface $entityManager)
    {
        $user = $this->getUser();
        if(!$user->isTotpAuthenticationEnabled()){
            $user->setTotpSecret($totpAuthenticator->generateSecret());

            $entityManager->persist($user);
            $entityManager->flush();
        }

        return $this->render('security/2fa-enable.html.twig', ['user' => $user]);
    }

    #[Route(path:'/authenticate/2fa/disable', name: 'app_authenticate_2fa_disable')]
    #[IsGranted('ROLE_USER')]
    public  function  disable2fa(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $user->setTotpSecret(null);

        if($user->getPreferred2faProvider() === 'totp')
        {
            $user->setPreferred2faProvider(null);
        }
        $entityManager->flush();

        $this->addFlash('success', 'Authenticator wurde deaktiviert');
        return $this->redirectToRoute('app_dashboard_2fa_settings');
    }

    #[Route(path: '/authenticate/2fa/qr-code', name: 'app_authenticate_2fa_qr_code')]
    #[IsGranted('ROLE_USER')]
    public function displayGoogleAuthenticatorQrCode(TotpAuthenticatorInterface $totpAuthenticator):Response
    {
        $qrCodeContent = $totpAuthenticator->getQRContent($this->getUser());

        $qrCode = new QrCode($qrCodeContent);
        $qrCode->setSize(300);

        $writer = new PngWriter();
        $qrCodeImage = $writer->write($qrCode);

        return new Response(
            $qrCodeImage->getString(),
            Response::HTTP_OK,
            ['Content-Type' => 'image/png']
        );
    }
}
