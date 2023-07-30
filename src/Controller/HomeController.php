<?php


namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;
use Doctrine\ORM\EntityManagerInterface;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class HomeController extends AbstractController
{   
    
    private function parseMtnMessage($message)
    {
        $pattern = '/Transferteffectuepour(?P<montant>\d+)FCFAa(?P<nom>\w+)\((?P<telephone>\d+)\)le(?P<date>\d{4}-\d{2}-\d{2})(?P<heure>\d{2}:\d{2}:\d{2}).Frais:(?P<frais>\d+)FCFA.Nouveausolde:(?P<nouveauSolde>\d+)FCFA,Reference:(?P<reference>\w+)\./';
    
        preg_match($pattern, $message, $matches);
    
        // Vérifier si au moins une correspondance a été trouvée
        if (!empty($matches)) {
            $result = [];
    
            // Vérifier si chaque champ a été trouvé et l'ajouter au résultat s'il existe
            if (isset($matches['montant'])) {
                $result['montant'] = $matches['montant'];
            }
            if (isset($matches['nom'])) {
                $result['nom'] = $matches['nom'];
            }
            if (isset($matches['telephone'])) {
                $result['telephone'] = $matches['telephone'];
            }
            if (isset($matches['agence'])) {
                $result['agence'] = $matches['agence'];
            }
            if (isset($matches['reference'])) {
                $result['reference'] = $matches['reference'];
            }
            if (isset($matches['date'])) {
                $result['date'] = $matches['date'];
            }
    
            return $result;
        }
    
        // Si aucune correspondance n'a été trouvée, retourner null
        return null;
    }
        
    private function extractInformationFromMoovText($text)
    {
        $result = [];
        // Exemple de modèle pour Moov
        // Vous pouvez ajouter d'autres champs si nécessaire (par exemple, le nom de l'agent)
        $pattern = '/Vousavezenvoye(?P<montant>\d+\.\d{2})FCFAalAgent(?P<agent>\d+)FRAIS:(?P<frais>\d+\.\d{2})FCFAvotrenouveausoldeMoovMoneyestde(?P<nouveauSolde>\d+\.\d{2})FCFA.Ref:(?P<reference>\d+)\./';
        
        preg_match($pattern, $text, $matches);

        if (!empty($matches)) {
                    $result = [];
        
                    // Vérifier si chaque champ a été trouvé et l'ajouter au résultat s'il existe
                    if (isset($matches['montant'])) {
                        $result['montant'] = $matches['montant'];
                    }
                    if (isset($matches['agent'])) {
                        $result['agent'] = $matches['agent'];
                    }
                    if (isset($matches['numero'])) {
                        $result['numero'] = $matches['numero'];
                    }
                    if (isset($matches['date'])) {
                        $result['date'] = $matches['date'];
                    }
                    if (isset($matches['frais'])) {
                        $result['frais'] = $matches['frais'];
                    }
                    if (isset($matches['nouveauSolde'])) {
                        $result['nouveauSolde'] = $matches['nouveauSolde'];
                    }
                    if (isset($matches['reference'])) {
                        $result['reference'] = $matches['reference'];
                    }
        
                    return $result;
        }
        return null;
    }


    #[Route('/', name: 'app_home', methods: ['GET','POST'])]
        
    public function uploadImage(Request $request, MailerInterface $mailer, EntityManagerInterface $entityManager)
    {
        $image = new Image();

        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {

            $uploadedFile = $form->get('url')->getData();
            // Générer un nom de fichier unique
            $newFilename = uniqid().'.'.$uploadedFile->guessExtension();

            // Déplacer le fichier vers le dossier 'assets/img'
            $uploadedFile->move(
                $this->getParameter('image_directory'),
                $newFilename
            );

            $image->setUrl('assets/imgRecuperes/'.$newFilename);
            
            $imagePath = $image->setUrl('assets/imgRecuperes/'.$newFilename);
            

            $tesseract = new TesseractOCR($imagePath->getUrl());
            $text = $tesseract->run(); 

            $chaine_formatee = str_replace("\n", " ", $text);
            $chaine_formatee = str_replace(" ", "", $chaine_formatee);

            $reseau = $image->getReseau();
            if ($reseau==="MTN") {
                $informations = $this->parseMtnMessage($chaine_formatee); 
                if ($informations) {  
                    // Utiliser les informations extraites selon vos besoins
                    $image->setNom($informations['nom']);
                    $image->setMontant($informations['montant']);
                    $image->setNumero($informations['telephone']);
                    $image->setDate($informations['date']);
                    $image->setReference($informations['reference']);

                    $email = (new Email())
                    ->from('rechargementbmose@gmail.com') // Adresse e-mail de l'expéditeur
                    ->to('ezechiasgdv@example.com') // Adresse e-mail du destinataire
                    ->subject('Notification de soumission de formulaire')
                    ->text('Le formulaire a été soumis avec succès.');

                    $mailer->send($email);
                } else {
                    $this->addFlash('error', "Rogner de l'image te tel sorte que seul le contenu du message soit visible.");
                }
            } else 
            if ($reseau==="MOOV") {
                    $informations = $this->extractInformationFromMoovText($chaine_formatee);
                    if ($informations) {                        
                        $image->setNom('MOOV');
                        $image->setMontant($informations['montant']);
                        $image->setNumero($informations['agent']);
                        $image->setReference($informations['reference']);
                        $image->setIdReference('Paiement');

                        $email = (new Email())
                        ->from('rechargementbmose@gmail.com') // Adresse e-mail de l'expéditeur
                        ->to('ezechiasgdv@example.com') // Adresse e-mail du destinataire
                        ->subject('Notification de soumission de formulaire')
                        ->text('Le formulaire a été soumis avec succès.');

                        $mailer->send($email);

                    } else {
                        $this->addFlash('error', "Rogner de l'image te tel sorte que seul le contenu du message soit visible.");
                    }

            };

            $entityManager->persist($image);
            $entityManager->flush();
            $this->addFlash('success', 'Votre formulaire a été soumis avec succès.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/index.html.twig', [
            'form' => $form->createView()
        ]);
    }
    
    #[Route('/tableau', name: 'app_tableau', methods: ['GET'])]
    public function index(ImageRepository $imageRepository): Response
    {
        return $this->render('home/tableau.html.twig', [
            'images' => $imageRepository->findAll(),
        ]);
    }

    
}
