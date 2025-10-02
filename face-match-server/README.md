This folder previously contained a face-match microservice scaffold. The service
was disabled and replaced with inert placeholders to avoid runtime or install
errors on Windows development machines. If you want to reintroduce the
microservice, create a fresh implementation or re-add the files and follow the
original README instructions.

Docker (recommended on Windows)

If you are on Windows and you don't want to install native build toolchains, use Docker:

1) Place required model files in `face-match-server/models` on the host.
2) From the face-match-server folder run:

  docker compose up --build

The service will be available at http://localhost:3333

