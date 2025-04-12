<?php

namespace App\Entity;

enum Statut: string
{
    case ACTIF = 'ACTIF';
    case INACTIF = 'INACTIF';
    case SUSPENDU = 'SUSPENDU';
}