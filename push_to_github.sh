#!/bin/bash

# Script para subir cambios a GitHub
# Ejecutar cuando tengas configuradas las credenciales de Git

echo "Subiendo cambios a GitHub..."
echo "Commit actual:"
git log --oneline -1

echo ""
echo "Archivos modificados en este commit:"
git show --name-only --pretty=""

echo ""
echo "Para hacer push, ejecuta:"
echo "git push origin main"

echo ""
echo "Si necesitas configurar credenciales:"
echo "git config --global user.name 'Tu Nombre'"
echo "git config --global user.email 'tu@email.com'"
echo ""
echo "Para usar token de acceso personal:"
echo "git remote set-url origin https://TOKEN@github.com/Konthim/issabel-esp32-gateway.git"