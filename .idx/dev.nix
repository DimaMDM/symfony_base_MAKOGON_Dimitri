# To learn more about how to use Nix to configure your environment
# see: https://developers.google.com/idx/guides/customize-idx-env
{ pkgs, ... }: {
  # Which nixpkgs channel to use.
  channel = "stable-23.11"; # or "unstable"
  # Use https://search.nixos.org/packages to find packages
  packages = [
    pkgs.php
    pkgs.symfony-cli
    pkgs.php82Packages.composer
  ];

  # Ajoute ce bloc pour lancer Postgres automatiquement
  services.postgres = {
    enable = true;
    enableTcp = true; # Crucial pour que 127.0.0.1 fonctionne
  };

  # Sets environment variables in the workspace
  env = {};
  idx = {
    # Search for the extensions you want on https://open-vsx.org/ and use "publisher.id"
    extensions = [
      "rangav.vscode-thunder-client"
    ];
    workspace = {
      onCreate = {
        # Open editors for the following files by default, if they exist:
        default.openFiles = ["public/index.php"];
      };
      # Runs when a workspace is (re)started
      onStart= {
        run-server = "symfony server:start";
      };
    };
  };
}