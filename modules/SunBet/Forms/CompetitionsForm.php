<?php

namespace SunAppModules\SunBet\Forms;

use SunAppModules\Core\src\FormBuilder\Form;

class CompetitionsForm extends Form
{
    public function buildForm()
    {
        $this->add('name', 'text', [
            'label' => 'Nazwa turnieju',
            'attr' => ['readonly' => true, 'disabled' => true]
        ]);
        $this->add('status', 'checkbox', [
            'label' => 'Włącz lub wyłącz turniej',
            'value' => 0,
            'attr' => ['readonly' => true, 'disabled' => true]
        ]);
        $this->add('sync', 'checkbox', [
            'label' => 'Włącz lub wyłącz synchronizację',
            'value' => 0,
            'attr' => ['readonly' => true, 'disabled' => true]
        ]);
        if ($this->getModel()->status == 0) {
            if (!$this->getModel()->is_ldap) {
                $this->modify('status', 'checkbox', [
                    'label' => 'Włącz lub wyłącz turniej',
                    'value' => 1,
                    'attr' => ['readonly' => false, 'disabled' => false]
                ], true);
                $this->modify('sync', 'checkbox', [
                    'label' => 'Włącz lub wyłącz synchronizację',
                    'value' => 1,
                    'attr' => ['readonly' => false, 'disabled' => false]
                ]);
            }
        }
    }
}
