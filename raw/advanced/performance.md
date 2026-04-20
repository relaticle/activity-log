# Performance Notes

> Over-fetch buffer, eager-loading, index tips.

- Every source batch-loads; no N+1 in the core path. Use `->with([...])` on `RelatedModelSource` if your renderer/title resolver reads relations.
- Pagination over-fetches by `perPage × (page + pagination_buffer)` per source so dedup/filtering stays correct at higher pages. Tune `pagination_buffer` if your sources rarely collide.
- `get()` is capped at 10 000 entries. For unbounded history, paginate.
- Add the `['subject_type', 'subject_id', 'created_at']` compound index on `activity_log`.
