<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

trait FormFieldTrait
{
    /**
     * Set field is rendered.
     *
     * @return bool
     */
    public function setRendered()
    {
        $this->rendered = true;
    }
}
