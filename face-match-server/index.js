// face-match microservice disabled in this workspace. This placeholder avoids
// runtime errors when node tries to require missing dependencies on Windows.

const express = require('express');
const app = express();

app.get('/', (req, res) => res.status(404).send('face-match-server disabled in repo'));
app.all('*', (req, res) => res.status(404).json({ error: 'service_disabled' }));

const port = process.env.PORT || 3333;
app.listen(port, () => console.log(`face-match-server placeholder listening on ${port}`));
