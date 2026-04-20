# Core Concepts

> The main building blocks — builder, sources, entries, renderers, and priority.

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
      Fluent builder that composes <strong>
        sources
      </strong>
      
      , applies <strong>
        filters
      </strong>
      
      , and returns paginated <code>
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
      
       objects from a specific origin (spatie log, related timestamps, custom closure). Implementations: <code>
        ActivityLogSource
      </code>
      
      , <code>
        RelatedActivityLogSource
      </code>
      
      , <code>
        RelatedModelSource
      </code>
      
      , <code>
        CustomEventSource
      </code>
      
      .
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
      Immutable value object describing a single event: <code>
        event
      </code>
      
      , <code>
        occurredAt
      </code>
      
      , <code>
        title
      </code>
      
      , <code>
        description
      </code>
      
      , <code>
        icon
      </code>
      
      , <code>
        color
      </code>
      
      , <code>
        subject
      </code>
      
      , <code>
        causer
      </code>
      
      , <code>
        relatedModel
      </code>
      
      , <code>
        properties
      </code>
      
      , plus an optional <code>
        renderer
      </code>
      
       key.
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
      
      . The default renderer handles every entry; you register custom renderers per <code>
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
      Each source carries a priority. When two entries share a <code>
        dedupKey
      </code>
      
      , the higher-priority one wins. Defaults: <code>
        activity_log
      </code>
      
      =10, <code>
        related_activity_log
      </code>
      
      =10, <code>
        related_model
      </code>
      
      =20, <code>
        custom
      </code>
      
      =30.
    </td>
  </tr>
</tbody>
</table>
