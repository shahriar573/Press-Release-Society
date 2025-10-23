<?php
// Simple search UI for testing api_search.php
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Search Tester</title>
  <style>
    body { font-family: Arial, sans-serif; max-width: 900px; margin: 40px auto; padding: 0 16px; }
    .row { display: flex; gap: 8px; align-items: center; }
    input, select { padding: 8px; }
    button { padding: 8px 14px; }
    pre { background: #f7f7f9; padding: 12px; border-radius: 6px; overflow: auto; }
    .hint { color: #666; }
  </style>
</head>
<body>
  <h1>🔎 Unified Search Tester</h1>
  <p class="hint">Tip: try queries like: Alice, News, Briefing, Daily, Published</p>
  <div class="row">
    <input id="q" placeholder="Search query" style="flex:2" />
    <select id="scope">
      <option value="all">All</option>
      <option value="Members">Members</option>
      <option value="PressReleases">PressReleases</option>
      <option value="MediaOutlets">MediaOutlets</option>
      <option value="DistributionRecords">DistributionRecords</option>
      <option value="Events">Events</option>
    </select>
    <input id="limit" type="number" value="25" min="1" max="100" style="width: 90px;" />
    <button onclick="runSearch()">Search</button>
  </div>
  <h3>Result</h3>
  <pre id="out">{ }</pre>

  <script>
    async function runSearch() {
      const q = document.getElementById('q').value.trim();
      const scope = document.getElementById('scope').value;
      const limit = document.getElementById('limit').value;
      const url = `api_search.php?q=${encodeURIComponent(q)}&scope=${encodeURIComponent(scope)}&limit=${encodeURIComponent(limit)}`;
      const r = await fetch(url);
      const j = await r.json();
      document.getElementById('out').textContent = JSON.stringify(j, null, 2);
    }
  </script>
</body>
</html>