<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Validator pour vérifier si une adresse existe
 */
class AddressValidator extends ConstraintValidator
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Valide qu'une adresse existe
     * 
     * @param mixed $value L'adresse à vérifier
     * @param Constraint $constraint Le contrainte pour la validation
     * @return void
     */
    public function validate($value, Constraint $constraint)
    {
        // Si l'adresse est vide, on ne fait rien
        if (null === $value || '' === $value) {
            return;
        }
        
        // Appel à l'API Nominatim pour vérifier l'adresse
        $endpoint = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($value) . '&format=json';
        $response = $this->client->request('GET', $endpoint);
        $data = $response->toArray();

        // Si l'adresse n'existe pas, on ajoute une violation
        if (! isset($data[0])) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}