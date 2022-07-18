<?php

/**
 * Created by SunGroup.
 * User: krzysztof.wieczorek
 */

namespace SunAppModules\Core\Repositories;

use SunAppModules\Core\Presenters\ApiPresenter;

class ApiRepository extends Repository // implements CacheableInterface
{
    public function boot()
    {
        parent::boot();
        $this->setPresenter(ApiPresenter::class);
    }
}
