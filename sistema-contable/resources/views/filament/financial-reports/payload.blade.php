<div class="space-y-3">
    <pre class="text-xs overflow-auto p-4 rounded-xl bg-gray-100 dark:bg-gray-900">
{{ json_encode($getRecord()->payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
    </pre>
</div>