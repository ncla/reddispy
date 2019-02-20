# Default directory upon ssh'ing into VM
echo "cd ~/code" >> ~/.bashrc

# Oh My Zsh
sudo apt install zsh
sh -c "$(wget https://raw.githubusercontent.com/robbyrussell/oh-my-zsh/master/tools/install.sh -O -)"