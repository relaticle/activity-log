# Database Schema

> Tables the plugin reads from and the indexes it needs.

<callout color="warning" icon="i-lucide-construction">

This page is a work-in-progress. A full schema walkthrough and ER diagram will land here; for now it lists the essentials.

</callout>

## Tables the plugin touches

Activity Log does **not** ship its own migrations. It reads from tables already present in your application.

<table>
<thead>
  <tr>
    <th>
      Table
    </th>
    
    <th>
      Owner
    </th>
    
    <th>
      Role
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <code>
        activity_log
      </code>
    </td>
    
    <td>
      <code>
        spatie/laravel-activitylog
      </code>
    </td>
    
    <td>
      Primary source of <code>
        activity_log
      </code>
      
       and <code>
        related_activity_log
      </code>
      
       entries.
    </td>
  </tr>
  
  <tr>
    <td>
      Your related tables
    </td>
    
    <td>
      Your app
    </td>
    
    <td>
      Sources registered via <code>
        fromRelation()
      </code>
      
       read their own timestamp columns.
    </td>
  </tr>
</tbody>
</table>

## Required index

For responsive timeline queries on `activity_log`, add this compound index:

```php
$table->index(['subject_type', 'subject_id', 'created_at']);
```

Without it, pagination over large logs will scan significantly more rows than necessary.

## Related-model columns

When you register `fromRelation('tasks', …)->event(column: 'completed_at', …)`, the plugin queries the related table (`tasks` in this example) filtered by the configured timestamp column. Make sure those columns are indexed if the related table is large.
