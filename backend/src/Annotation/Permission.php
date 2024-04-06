<?php
namespace App\Annotation;


/**
 * @Annotation
 */
#[\Attribute(\Attribute::IS_REPEATABLE | \Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class Permission
{

    public function __construct(
        public string $group,
        public string $action
    )
    {

    }


    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return mixed
     */
    public function getGroup()
    {
        return $this->group;
    }




}