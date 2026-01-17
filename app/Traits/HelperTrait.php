<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Str;

/**
 * Helper trait for common service operations.
 * Includes slug generation, validation, and ordering management.
 */
trait HelperTrait
{
    /**
     * Generate a unique slug from a string.
     *
     * @param string $text The text to convert to slug
     * @param string $modelClass The model class to check uniqueness against
     * @param string $slugColumn The column name for slug (default: 'slug')
     * @param string $idColumn The column name for ID (default: model's primary key)
     * @param int|null $excludeId ID to exclude from uniqueness check
     * @return string
     */
    protected function generateUniqueSlug(
        string $text,
        string $modelClass,
        string $slugColumn = 'slug',
        ?string $idColumn = null,
        ?int $excludeId = null
    ): string {
        $slug = Str::slug($text);
        $originalSlug = $slug;
        $counter = 1;

        // Get the primary key column name if not provided
        if ($idColumn === null) {
            $model = new $modelClass();
            $idColumn = $model->getKeyName();
        }

        while (true) {
            $query = $modelClass::where($slugColumn, $slug);

            if ($excludeId) {
                $query->where($idColumn, '!=', $excludeId);
            }

            if (!$query->exists()) {
                return $slug;
            }

            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }

    /**
     * Ensure slug is unique.
     *
     * @param string $slug The slug to check
     * @param string $modelClass The model class to check uniqueness against
     * @param string $slugColumn The column name for slug (default: 'slug')
     * @param string $idColumn The column name for ID (default: model's primary key)
     * @param int|null $excludeId ID to exclude from uniqueness check
     * @return string
     */
    protected function ensureUniqueSlug(
        string $slug,
        string $modelClass,
        string $slugColumn = 'slug',
        ?string $idColumn = null,
        ?int $excludeId = null
    ): string {
        return $this->generateUniqueSlug($slug, $modelClass, $slugColumn, $idColumn, $excludeId);
    }

    /**
     * Validate that a field value is unique.
     *
     * @param string $value The value to check
     * @param string $modelClass The model class to check uniqueness against
     * @param string $column The column name to check (default: 'name')
     * @param string $idColumn The column name for ID (default: model's primary key)
     * @param int|null $excludeId ID to exclude from uniqueness check
     * @param string $fieldName The field name for error message (default: 'Field')
     * @return void
     * @throws Exception
     */
    protected function validateUnique(
        string $value,
        string $modelClass,
        string $column = 'name',
        ?string $idColumn = null,
        ?int $excludeId = null,
        string $fieldName = 'Field'
    ): void {
        // Get the primary key column name if not provided
        if ($idColumn === null) {
            $model = new $modelClass();
            $idColumn = $model->getKeyName();
        }

        $query = $modelClass::where($column, $value);

        if ($excludeId) {
            $query->where($idColumn, '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new Exception("{$fieldName} '{$value}' already exists.", 422);
        }
    }

    /**
     * Validate that a name is unique.
     * Convenience method for name validation.
     *
     * @param string $name The name to check
     * @param string $modelClass The model class to check uniqueness against
     * @param string $idColumn The column name for ID (default: model's primary key)
     * @param int|null $excludeId ID to exclude from uniqueness check
     * @return void
     * @throws Exception
     */
    protected function validateUniqueName(
        string $name,
        string $modelClass,
        ?string $idColumn = null,
        ?int $excludeId = null
    ): void {
        $this->validateUnique($name, $modelClass, 'name', $idColumn, $excludeId, 'Name');
    }

    /**
     * Get next available order for a model within a parent scope.
     *
     * @param string $modelClass The model class
     * @param string $parentColumn The parent foreign key column (e.g., 'course_id', 'unit_id', 'lesson_id')
     * @param mixed $parentId The parent ID value
     * @param string $orderColumn The order column name (e.g., 'unit_order', 'lesson_order', 'display_order')
     * @return int
     */
    protected function getNextOrder(
        string $modelClass,
        string $parentColumn,
        $parentId,
        string $orderColumn
    ): int {
        $maxOrder = $modelClass::where($parentColumn, $parentId)
            ->max($orderColumn);

        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Validate that order is unique within a parent scope.
     *
     * @param string $modelClass The model class
     * @param string $parentColumn The parent foreign key column (e.g., 'course_id', 'unit_id', 'lesson_id')
     * @param mixed $parentId The parent ID value
     * @param int $order The order value to validate
     * @param string $orderColumn The order column name (e.g., 'unit_order', 'lesson_order', 'display_order')
     * @param string $idColumn The model's primary key column
     * @param int|null $excludeId ID to exclude from validation
     * @param string $entityName The entity name for error message (e.g., 'Unit', 'Lesson', 'ContentItem')
     * @return void
     * @throws Exception
     */
    protected function validateOrder(
        string $modelClass,
        string $parentColumn,
        $parentId,
        int $order,
        string $orderColumn,
        ?string $idColumn = null,
        ?int $excludeId = null,
        string $entityName = 'Entity'
    ): void {
        // Get the primary key column name if not provided
        if ($idColumn === null) {
            $model = new $modelClass();
            $idColumn = $model->getKeyName();
        }

        $query = $modelClass::where($parentColumn, $parentId)
            ->where($orderColumn, $order);

        if ($excludeId) {
            $query->where($idColumn, '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new Exception("{$entityName} order {$order} already exists.", 422);
        }
    }

    /**
     * Shift orders when moving an item to a new position.
     *
     * @param string $modelClass The model class
     * @param string $parentColumn The parent foreign key column (e.g., 'course_id', 'unit_id', 'lesson_id')
     * @param mixed $parentId The parent ID value
     * @param int $oldOrder The current order position
     * @param int $newOrder The new order position
     * @param string $orderColumn The order column name (e.g., 'unit_order', 'lesson_order', 'display_order')
     * @param string $idColumn The model's primary key column
     * @param mixed $itemId The ID of the item being moved
     * @return void
     */
    protected function shiftOrders(
        string $modelClass,
        string $parentColumn,
        $parentId,
        int $oldOrder,
        int $newOrder,
        string $orderColumn,
        ?string $idColumn = null,
        $itemId = null
    ): void {
        if ($oldOrder === $newOrder) {
            return;
        }

        // Get the primary key column name if not provided
        if ($idColumn === null) {
            $model = new $modelClass();
            $idColumn = $model->getKeyName();
        }

        if ($oldOrder < $newOrder) {
            // Moving down: shift items between old and new positions up
            $modelClass::where($parentColumn, $parentId)
                ->where($orderColumn, '>', $oldOrder)
                ->where($orderColumn, '<=', $newOrder)
                ->where($idColumn, '!=', $itemId)
                ->decrement($orderColumn);
        } else {
            // Moving up: shift items between new and old positions down
            $modelClass::where($parentColumn, $parentId)
                ->where($orderColumn, '>=', $newOrder)
                ->where($orderColumn, '<', $oldOrder)
                ->where($idColumn, '!=', $itemId)
                ->increment($orderColumn);
        }
    }
}
