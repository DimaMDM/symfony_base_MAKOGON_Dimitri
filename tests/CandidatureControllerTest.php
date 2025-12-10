<?php

namespace App\Tests;

use App\Entity\Candidate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class CandidatureControllerTest extends WebTestCase
{
    private ?EntityManagerInterface $entityManager = null;

    protected function setUp(): void
    {
        parent::setUp();
        self::ensureKernelShutdown(); // Ensure the kernel is shut down from previous tests
        self::bootKernel(['environment' => 'test']); // Boot the kernel for a test environment
        $container = self::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();

        // Start a transaction for the test, allowing rollback
        $this->entityManager->getConnection()->setAutocommit(false);
        $this->entityManager->getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        // Rollback the transaction to clean up database changes
        if ($this->entityManager->getConnection()->isTransactionActive()) {
            $this->entityManager->getConnection()->rollBack();
        }

        $this->entityManager->close();
        $this->entityManager = null; // Avoid memory leaks
        parent::tearDown();
    }

    public function testMultiStepCandidatureFormSubmission(): void
    {
        $client = static::createClient(['environment' => 'test']); // Use test environment

        // 1. Access the /apply route (Step 1)
        $crawler = $client->request('GET', '/apply');
        $this->assertResponseIsSuccessful('GET /apply failed');
        $this->assertSelectorTextContains('h2.card-title', 'Postuler - Étape 1 sur 5');

        // 2. Fill and submit Step 1 data
        $form = $crawler->selectButton('Suivant')->form([
            'candidature[firstName]' => 'Test',
            'candidature[lastName]' => 'User',
            'candidature[email]' => 'test.user@example.com',
            'candidature[phone]' => '0123456789',
            'candidature[hasExperience]' => 1, // Yes
        ]);
        $client->submit($form);

        // 3. Assert redirection to Step 2
        $this->assertResponseRedirects('/apply');
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Redirect to Step 2 failed');
        $this->assertSelectorTextContains('h2.card-title', 'Postuler - Étape 2 sur 5');

        // 4. Fill and submit Step 2 data
        $form = $crawler->selectButton('Suivant')->form([
            'candidature[experienceDetails]' => '2 years as a Symfony developer.',
        ]);
        $client->submit($form);

        // 5. Assert redirection to Step 3
        $this->assertResponseRedirects('/apply');
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Redirect to Step 3 failed');
        $this->assertSelectorTextContains('h2.card-title', 'Postuler - Étape 3 sur 5');

        // 6. Fill and submit Step 3 data (immediately available)
        $form = $crawler->selectButton('Suivant')->form([
            'candidature[isImmediatelyAvailable]' => 1, // Yes
            // If immediately available, availabilityDate is not strictly required by validation
        ]);
        $client->submit($form);

        // 7. Assert redirection to Step 4
        $this->assertResponseRedirects('/apply');
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Redirect to Step 4 failed');
        $this->assertSelectorTextContains('h2.card-title', 'Postuler - Étape 4 sur 5');

        // 8. Fill and submit Step 4 data (consent RGPD)
        $form = $crawler->selectButton('CONFIRMER LA MISSION')->form([ // Button text for last step
            'candidature[consentRGPD]' => 1, // Yes
        ]);
        $client->submit($form);

        // 9. Assert redirection to the success page
        $this->assertResponseRedirects('/success/' . $this->getLatestCandidateId()); // Get the ID of the newly created candidate
        $crawler = $client->followRedirect();
        $this->assertResponseIsSuccessful('Redirect to success page failed');

        // 10. Assert success message and candidate data on the success page
        $this->assertSelectorTextContains('h2.card-title', 'Candidature envoyée avec succès !');
        $this->assertSelectorTextContains('li:contains("Prénom : Test")', 'Prénom : Test');
        $this->assertSelectorTextContains('li:contains("Nom : User")', 'Nom : User');
        $this->assertSelectorTextContains('li:contains("Email : test.user@example.com")', 'Email : test.user@example.com');
        $this->assertSelectorTextContains('li:contains("Téléphone : 0123456789")', 'Téléphone : 0123456789');
        $this->assertSelectorTextContains('li:contains("Expérience : Oui")', 'Expérience : Oui');
        $this->assertSelectorTextContains('li:contains("Détails de l\'expérience :")', '2 years as a Symfony developer.');
        $this->assertSelectorTextContains('li:contains("Disponibilité : Immédiate")', 'Disponibilité : Immédiate');
        $this->assertSelectorTextContains('li:contains("Consentement RGPD : Accepté")', 'Consentement RGPD : Accepté');


        // 11. Verify that a new Candidate entity has been persisted in the database
        $candidateRepository = $this->entityManager->getRepository(Candidate::class);
        $candidate = $candidateRepository->findOneBy(['email' => 'test.user@example.com']);

        $this->assertNotNull($candidate, 'Candidate should be found in the database.');
        $this->assertEquals('Test', $candidate->getFirstName());
        $this->assertEquals('User', $candidate->getLastName());
        $this->assertEquals('test.user@example.com', $candidate->getEmail());
        $this->assertEquals('0123456789', $candidate->getPhone());
        $this->assertTrue($candidate->isHasExperience());
        $this->assertEquals('2 years as a Symfony developer.', $candidate->getExperienceDetails());
        $this->assertTrue($candidate->isIsImmediatelyAvailable());
        $this->assertEquals('submitted', $candidate->getStatus());
        $this->assertTrue($candidate->isConsentRGPD());
    }

    private function getLatestCandidateId(): int
    {
        $candidateRepository = $this->entityManager->getRepository(Candidate::class);
        // Find the latest candidate by ID (assuming auto-incrementing IDs)
        $latestCandidate = $candidateRepository->findOneBy([], ['id' => 'DESC']);

        return $latestCandidate ? $latestCandidate->getId() : 0;
    }
}