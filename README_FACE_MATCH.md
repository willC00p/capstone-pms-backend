Face match integration
======================

Note: face verification/matching was intentionally removed from this repository. There is no active face-matching service or routes. If you want to reintroduce face verification later, please open an issue describing the desired approach (cloud provider, self-hosted microservice, or native PHP extension).
This repository includes a small optional Node.js microservice that performs face detection and descriptor-based matching using a port of face-api.js. The Laravel backend provides a proxy endpoint so the mobile client can upload images to a single API.

