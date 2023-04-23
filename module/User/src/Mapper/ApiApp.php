<?php

declare(strict_types=1);

namespace User\Mapper;

use Application\Mapper\BaseMapper;
use User\Model\ApiApp as ApiAppModel;

/**
 * @template-extends BaseMapper<ApiAppModel>
 */
class ApiApp extends BaseMapper
{
    /**
     * @param string $appId
     *
     * @return ApiAppModel|null
     */
    public function findByAppId(string $appId): ?ApiAppModel
    {
        return $this->getRepository()->findOneBy(
            [
                'appId' => $appId,
            ]
        );
    }

    /**
     * @inheritDoc
     */
    protected function getRepositoryName(): string
    {
        return ApiAppModel::class;
    }
}
