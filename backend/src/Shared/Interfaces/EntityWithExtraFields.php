<?php

namespace App\Shared\Interfaces;

use App\Entity\Appointment\Appointment;
use App\Entity\ExtraAppointmentField\ExtraAppointmentField;
use Doctrine\Common\Collections\Collection;

interface EntityWithExtraFields
{
    public function getExtraAppointmentFields(): Collection;

    public function addExtraAppointmentField(ExtraAppointmentField $extraAppointmentField): self;

    public function extraFieldValueByTitle(string $name): string;

    public function removeAllAppointmentExtraFields(): self;


}