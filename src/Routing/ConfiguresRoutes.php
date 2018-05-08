<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Routing;

trait ConfiguresRoutes
{
    /**
     * @var string
     */
    protected $indexRouteName;

    /**
     * @var string
     */
    protected $createRouteName;

    /**
     * @var string
     */
    protected $viewRouteName;

    /**
     * @var string
     */
    protected $updateRouteName;

    /**
     * @var string
     */
    protected $deleteRouteName;

    protected $sortUrl;

    /**
     * @return mixed
     */
    public function getIndexRouteName(): string
    {
        return $this->indexRouteName;
    }

    public function getIndexUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @param mixed $indexRouteName
     */
    public function setIndexRouteName(string $indexRouteName): void
    {
        $this->indexRouteName = $indexRouteName;
    }

    public function getSortUrl(string $key, string $direction)
    {
        $this->setSortUrl($key, $direction);
        return $this->sortUrl;
    }

    protected function setSortUrl(string $key, string $direction)
    {
        $this->sortUrl = route($this->getIndexRouteName(), add_query_param([
            $this->getGridSortParam() => $key,
            $this->getGridSortDirParam() => $direction
        ]));
    }

    /**
     * @return mixed
     */
    public function getCreateRouteName(): string
    {
        return $this->createRouteName;
    }

    public function getCreateUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    public function getSearchUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @param mixed $createRouteName
     */
    public function setCreateRouteName(string $createRouteName): void
    {
        $this->createRouteName = $createRouteName;
    }

    /**
     * @return mixed
     */
    public function getViewRouteName(): string
    {
        return $this->viewRouteName;
    }

    /**
     * @param mixed $viewRouteName
     */
    public function setViewRouteName(string $viewRouteName): void
    {
        $this->viewRouteName = $viewRouteName;
    }

    public function getViewUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @return mixed
     */
    public function getUpdateRouteName(): string
    {
        return $this->updateRouteName;
    }

    /**
     * @param mixed $updateRouteName
     */
    public function setUpdateRouteName(string $updateRouteName): void
    {
        $this->updateRouteName = $updateRouteName;
    }

    public function getUpdateUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @return mixed
     */
    public function getDeleteRouteName(): string
    {
        return $this->deleteRouteName;
    }

    public function getDeleteUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @param mixed $deleteRouteName
     */
    public function setDeleteRouteName(string $deleteRouteName): void
    {
        $this->deleteRouteName = $deleteRouteName;
    }
}