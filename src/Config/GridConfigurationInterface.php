<?php
/**
 * Copyright (c) 2018.
 * @author Antony [leantony] Chacha
 */

namespace Leantony\Grid\Config;

interface GridConfigurationInterface
{
    public function getGridView(): string;

    public function getSearchView(): string;

    public function getPaginationView(): string;

    public function getPaginationPageSize(): int;

    public function getPaginationFunction(): string;

    public function getGridFilterQueryType(): string;

    public function getGridSearchQueryType(): string;

    public function getSearchParam(): string;

    public function getSortParam(): string;

    public function getSortDirections(): array;

    public function getLabelNamePattern(): string;

    public function getToolbarSize(): array;

    public function shouldWarnIfEmpty(): bool;

    public function getGridDefaultClass(): string;

    public function getColumnsToSkipOnFilter(): array;

    public function getSortDirParam(): string;

    public function getExportParam(): string;

    public function getGridExportTypes(): array;

    public function getMaxRowsForExport(): int;
}