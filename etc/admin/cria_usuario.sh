#!/bin/bash
# Cria um usuário através de um script, sem interação humana
#
sudo adduser jhondoe --gecos "Jhon Doe,,," --disabled-password
echo "jhondoe:senha123" | sudo chpasswd
gpasswd -a jhondoe sudo