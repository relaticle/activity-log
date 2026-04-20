# Core Concepts

> Builder, sources, entries, and renderers.

<table>
<thead>
  <tr>
    <th>
      Concept
    </th>
    
    <th>
      What it represents
    </th>
  </tr>
</thead>

<tbody>
  <tr>
    <td>
      <strong>
        <code>
          TimelineBuilder
        </code>
      </strong>
    </td>
    
    <td>
      Fluent builder that composes sources, applies filters, and returns paginated <code>
        TimelineEntry
      </code>
      
       collections. Built per-record via <code>
        $record->timeline()
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        <code>
          TimelineSource
        </code>
      </strong>
    </td>
    
    <td>
      Produces <code>
        TimelineEntry
      </code>
      
       objects from a single origin - spatie log, related timestamps, or custom closure.
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        <code>
          TimelineEntry
        </code>
      </strong>
    </td>
    
    <td>
      Immutable value object describing one event (<code>
        event
      </code>
      
      , <code>
        occurredAt
      </code>
      
      , <code>
        title
      </code>
      
      , <code>
        causer
      </code>
      
      , <code>
        properties
      </code>
      
      , …).
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        <code>
          TimelineRenderer
        </code>
      </strong>
    </td>
    
    <td>
      Converts a <code>
        TimelineEntry
      </code>
      
       into a Blade <code>
        View
      </code>
      
       or <code>
        HtmlString
      </code>
      
      . Register custom ones per <code>
        event
      </code>
      
       or <code>
        type
      </code>
      
      .
    </td>
  </tr>
  
  <tr>
    <td>
      <strong>
        Priority
      </strong>
    </td>
    
    <td>
      Each source carries a priority; on <code>
        dedupKey
      </code>
      
       collisions the higher one wins.
    </td>
  </tr>
</tbody>
</table>

## Default source priorities

<table>
<thead>
  <tr>
    <th>
      Source
    </th>
    
    <th>
      Priority
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
      10
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        related_activity_log
      </code>
    </td>
    
    <td>
      10
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        related_model
      </code>
    </td>
    
    <td>
      20
    </td>
  </tr>
  
  <tr>
    <td>
      <code>
        custom
      </code>
    </td>
    
    <td>
      30
    </td>
  </tr>
</tbody>
</table>
