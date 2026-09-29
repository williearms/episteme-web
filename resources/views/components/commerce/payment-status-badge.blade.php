@props(['status'])

<x-ui.badge :variant="$status->badgeVariant()" {{ $attributes }}>{{ $status->label() }}</x-ui.badge>
