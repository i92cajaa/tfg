<?php

namespace App\Shared\Interfaces;

use App\Entity\Appointment\Appointment;
use App\Entity\ExtraAppointmentField\ExtraAppointmentField;
use App\Entity\Template\TemplateType;
use Doctrine\Common\Collections\Collection;

interface EntityWithTemplates
{
    public function getTemplates(): Collection;

    public function getTemplatesByTemplateType(TemplateType $templateType): array;


}