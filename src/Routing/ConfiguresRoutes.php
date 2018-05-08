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
    
    /**
    * @var string
    */
    protected $sortUrl;

    /**
     * @return string
     */
    public function getIndexRouteName(): string
    {
        return $this->indexRouteName;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getIndexUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @param string $indexRouteName
     */
    public function setIndexRouteName(string $indexRouteName): void
    {
        $this->indexRouteName = $indexRouteName;
    }

    /**
     * @param string $key
     * @param string $direction
     * @return string
     */
    public function getSortUrl(string $key, string $direction): string
    {
        $this->setSortUrl($key, $direction);
        return $this->sortUrl;
    }

    /**
     * @param string $key
     * @param string $direction
     */
    protected function setSortUrl(string $key, string $direction): void
    {
        $this->sortUrl = route($this->getIndexRouteName(), add_query_param([
            $this->getGridSortParam() => $key,
            $this->getGridSortDirParam() => $direction
        ]));
    }

    /**
     * @return string
     */
    public function getCreateRouteName(): string
    {
        return $this->createRouteName;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getCreateUrl(array $params = []): string
    {
        return route($this->getCreateRouteName(), add_query_param($params));
    }

    /**
     * @param array $params
     * @return string
     */
    public function getSearchUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @param string $createRouteName
     */
    public function setCreateRouteName(string $createRouteName): void
    {
        $this->createRouteName = $createRouteName;
    }

    /**
     * @return string
     */
    public function getViewRouteName(): string
    {
        return $this->viewRouteName;
    }

    /**
     * @param string $viewRouteName
     */
    public function setViewRouteName(string $viewRouteName): void
    {
        $this->viewRouteName = $viewRouteName;
    }

    public function getViewUrl(array $params = []): string
    {
        return route($this->getViewRouteName(), add_query_param($params));
    }

    /**
     * @return string
     */
    public function getUpdateRouteName(): string
    {
        return $this->updateRouteName;
    }

    /**
     * @param string $updateRouteName
     */
    public function setUpdateRouteName(string $updateRouteName): void
    {
        $this->updateRouteName = $updateRouteName;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getUpdateUrl(array $params = []): string
    {
        return route($this->getIndexRouteName(), add_query_param($params));
    }

    /**
     * @return string
     */
    public function getDeleteRouteName(): string
    {
        return $this->deleteRouteName;
    }

    /**
     * @param array $params
     * @return string
     */
    public function getDeleteUrl(array $params = []): string
    {
        return route($this->getDeleteRouteName(), add_query_param($params));
    }

    /**
     * @param string $deleteRouteName
     */
    public function setDeleteRouteName(string $deleteRouteName): void
    {
        $this->deleteRouteName = $deleteRouteName;
    }
}
