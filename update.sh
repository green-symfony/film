git fetch origin main
git checkout main -f && git merge origin/main --no-ff -Xtheris -m'update(auto merge with the origin/main branch)'

./init.sh