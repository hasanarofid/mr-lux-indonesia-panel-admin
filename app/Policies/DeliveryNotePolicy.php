<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryNote;
use Illuminate\Auth\Access\HandlesAuthorization;

class DeliveryNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_delivery_note') || 
               $user->can('view_any_automatic::delivery::note') || 
               $user->can('view_any_manual::delivery::note');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DeliveryNote $deliveryNote): bool
    {
        return $user->can('view_delivery_note') || 
               $user->can('view_automatic::delivery::note') || 
               $user->can('view_manual::delivery::note');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_delivery_note') || 
               $user->can('create_automatic::delivery::note') || 
               $user->can('create_manual::delivery::note');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DeliveryNote $deliveryNote): bool
    {
        if ($deliveryNote->status === 'DELIVERED') {
            return false;
        }

        $resourcePrefix = $deliveryNote->type === 'AUTOMATIC' ? 'automatic' : 'manual';
        
        return $user->can('update_delivery_note') || 
               $user->can("update_{$resourcePrefix}::delivery::note");
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DeliveryNote $deliveryNote): bool
    {
        if ($deliveryNote->status === 'DELIVERED') {
            return false;
        }

        $resourcePrefix = $deliveryNote->type === 'AUTOMATIC' ? 'automatic' : 'manual';

        return $user->can('delete_delivery_note') || 
               $user->can("delete_{$resourcePrefix}::delivery::note");
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_delivery_note') || 
               $user->can('delete_any_automatic::delivery::note') || 
               $user->can('delete_any_manual::delivery::note');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DeliveryNote $deliveryNote): bool
    {
        $resourcePrefix = $deliveryNote->type === 'AUTOMATIC' ? 'automatic' : 'manual';

        return $user->can('force_delete_delivery_note') || 
               $user->can("force_delete_{$resourcePrefix}::delivery::note");
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_delivery_note') || 
               $user->can('force_delete_any_automatic::delivery::note') || 
               $user->can('force_delete_any_manual::delivery::note');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DeliveryNote $deliveryNote): bool
    {
        $resourcePrefix = $deliveryNote->type === 'AUTOMATIC' ? 'automatic' : 'manual';

        return $user->can('restore_delivery_note') || 
               $user->can("restore_{$resourcePrefix}::delivery::note");
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_delivery_note') || 
               $user->can('restore_any_automatic::delivery::note') || 
               $user->can('restore_any_manual::delivery::note');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DeliveryNote $deliveryNote): bool
    {
        $resourcePrefix = $deliveryNote->type === 'AUTOMATIC' ? 'automatic' : 'manual';

        return $user->can('replicate_delivery_note') || 
               $user->can("replicate_{$resourcePrefix}::delivery::note");
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_delivery_note') || 
               $user->can('reorder_automatic::delivery::note') || 
               $user->can('reorder_manual::delivery::note');
    }
}
