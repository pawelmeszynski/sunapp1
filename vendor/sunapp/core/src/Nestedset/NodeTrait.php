<?php

namespace SunAppModules\Core\src\Nestedset;

use Baum\Node;
use Kalnoy\Nestedset\NodeTrait as BaseNodeTrait;

trait NodeTrait
{
    use BaseNodeTrait;

    /**
     * Sign on model events.
     */
    public static function bootNodeTrait()
    {
        static::saving(function ($model) {
            return $model->callPendingAction();
        });

        static::deleting(function ($model) {
            // We will need fresh data to delete node safely
            $model->refreshNode();
        });

        static::deleted(function ($model) {
            $model->deleteDescendants();
        });

        if (static::usesSoftDelete()) {
            static::restoring(function ($model) {
                static::$deletedAt = $model->{$model->getDeletedAtColumn()};
            });

            static::restored(function ($model) {
                $model->restoreDescendants(static::$deletedAt);
            });
        }

        static::saved(function ($model) {
            $model->setDepth();
        });
    }

    /**
     * Sets the depth attribute
     *
     * @return Node
     */
    public function setDepth()
    {
        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $level = $self->getLevel();

            $self->newNestedSetQuery()->where(
                $self->getKeyName(),
                '=',
                $self->getKey()
            )->update([$self->getDepthName() => $level]);
            $self->setAttribute($self->getDepthName(), $level);
        });

        return $this;
    }

    /**
     * Returns the level of this node in the tree.
     * Root level is 0.
     *
     * @return int
     */
    public function getLevel()
    {
        if (is_null($this->getParentId())) {
            return 0;
        }

        return $this->computeLevel();
    }

    /**
     * Compute current node level. If could not move past ourseleves return
     * our ancestor count, otherwhise get the first parent level + the computed
     * nesting.
     *
     * @return int
     */
    protected function computeLevel()
    {
        list($node, $nesting) = $this->determineDepth($this);

        if ($node->equals($this)) {
            return $this->ancestors()->count();
        }

        return $node->getDepth() + $nesting;
    }

    /**
     * Return an array with the last node we could reach and its nesting level
     *
     * @param  Baum\Node  $node
     * @param  int  $nesting
     * @return  array
     */
    protected function determineDepth($node, $nesting = 0)
    {
        // Traverse back up the ancestry chain and add to the nesting level count
        while ($parent = $node->parent()->first()) {
            $nesting = $nesting + 1;

            $node = $parent;
        }

        return [$node, $nesting];
    }

    /**
     * Get the depth key name.
     *
     * @return  string
     */
    public function getDepthName()
    {
        return NestedSet::DEPTH;
    }

    /**
     * Sets the depth attribute for the current node and all of its descendants.
     *
     * @return Node
     */
    public function setDepthWithSubtree()
    {
        $self = $this;

        $this->getConnection()->transaction(function () use ($self) {
            $self->reload();

            $self->descendantsAndSelf()->select($self->getKeyName())->lockForUpdate()->get();

            $oldDepth = !is_null($self->getDepth()) ? $self->getDepth() : 0;

            $newDepth = $self->getLevel();

            $self->newNestedSetQuery()->where(
                $self->getKeyName(),
                '=',
                $self->getKey()
            )->update([$self->getDepthName() => $newDepth]);
            $self->setAttribute($self->getDepthName(), $newDepth);

            $diff = $newDepth - $oldDepth;
            if (!$self->isLeaf() && $diff != 0) {
                $self->descendants()->increment($self->getDepthName(), $diff);
            }
        });

        return $this;
    }

    /**
     * Get the value of the model's depth key.
     *
     * @return  int
     */
    public function getDepth()
    {
        return $this->getAttributeValue($this->getDepthName());
    }

    /**
     * Equals?
     *
     * @param  Node
     * @return bool
     */
    public function equals($node)
    {
        return $this == $node;
    }

    /**
     * {@inheritdoc}
     *
     * @since 2.0
     */
    public function newEloquentBuilder($query)
    {
        return new QueryBuilder($query);
    }

    public function parentRoot($skip = 0)
    {
        if (is_null($this->parent_id)) {
            return null;
        }
        if ($skip === 0) {
            return $this->where('entity', get_class($this))->whereNull('parent_id')
                ->where($this->getLftName(), '<', $this->getLft())->where($this->getRgtName(), '>', $this->getRgt())
                ->first();
        }
        $parents = $this->where('entity', get_class($this))->where($this->getLftName(), '<', $this->getLft())
            ->where($this->getRgtName(), '>', $this->getRgt())
            ->get();

        return $parents->skip($skip)->first();
    }
}
