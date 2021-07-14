# Bienvenue sur mon projet de création d'un Service On Demand de déploiement d'une application PHP-MySQL sur un cluster Kubernetes avec GitLab
Il s'agit d'un projet réalisé en Juillet 2021 dans le cadre de ma formation "Expert DevOps" chez OpenClassRooms.

## AVERTISSEMENT
Il s'agit seulement d'un projet d'étude, à NE PAS UTILISER EN PROD  !!!

## Ca fait quoi ?
Dans le détails, il s'agit de mettre en place un Service On Demand, composé essentiellement d'un Pipeline de CI/CD GitLab, qui pourra être mis à disposition des développeurs, pour leur permettre de développer leur application PHP-MySQL avec GitLab CI/CD, et de la voir déployée automatiquement sur un cluster Kubernetes.

Le Pipeline du Service On Demand n'est pas prévu pour être exécuté directement, mais à la demande, grâce à un trigger ( déclencheur ) depuis un autre Pipeline, celui du développeur.

Lorsque le Pipeline du Service On Demand est déclenché, il va :
- préparer un manisfeste Kubernetes de secrets pour la gestion des crédentials MySQL
- préparer une image Docker MySQL et la pousser sur le Docker Hub
- récupérer l'image Docker de l'application du développeur depuis la Container Registry intégrée à GitLab
- envoyer cette image sur le Docker Hub
- créer des objets Kubernetes de `deployment` et de `service` pour l'application PHP et MySQL, et d'autres objets Kubernetes utiles, sur un cluster distant, en utilisant les images stockées sur le Docker Hub.
- les 2 pods PHP créés peuvent accéder au pod MySQL
- les credentials MySQL seront accessibles en variables d'environnement depuis les pods PHP ( pour les noms, voir ci-dessous : variables de CI/CD )
- le pod MySQL utilise un stockage "persistant" basique pour les données, basé sur un volume anonyme Docker

## Ca ressemble à quoi ?
![Vue d'ensemble du Processus du Service On Demand](https://github.com/alinuxien/service-on-demand/blob/master/Service%20On%20Demand.png)

## Contenu ?
- Un pipeline de CI/CD Gitlab : `gitlab-ci.yml` 
- Le fichier `secrets.yaml` qui définit un objet Kubernetes pour stocker les credentials MySQL de façon sécurisée
- Le fichier `Dockerfile-mysql` pour créer l'image du Container Docker de serveur MySQL
- Le fichier `configmap.yaml` pour rendre disponible l'accès au service MySQL
- Le fichier `mysql.yaml` pour créer le déploiement et le service Kubernetes pour MySQL
- Le fichier `php.yaml` pour créer le déploiement et le service Kubernetes pour l'application PHP

 
## J'ai besoin de quoi ?
- Une VM GitLab locale, avec certains utilitaires, et un Runner de type Shell. 
- Un Cluster Kubernetes, avec un accès configuré depuis la ligne de commande **de l'utilisateur `vagrant` de la vm** ( kubectl )
- Un repository Docker Hub pour stocker les images PHP et MySQL
 
Vous pouvez trouver de quoi créer tout cela sur mon projet [k8s-aws-iac](https://github.com/alinuxien/k8s-aws-iac)

## Comment ça s'utilise ?
Dans GitLab :
- vous devez créer un nouveau projet nommé `Service On Demand` et y déposer le contenu de ce dépot ( `https://github.com/alinuxien/service-on-demand` )
- vous devez ensuite créer des variables de CI/CD pour renseigner les crédentials Docker Hub, ainsi que MySQL : 
- Dans le projet, allez dans le menu de gauche, Settings -> CI/CD, puis développez les `Variables`, et créez : 
- CI_REGISTRY **en masqué** : docker.io
- CI_REGISTRY_IMAGE : index.docker.io/*username*/*repository*
- CI_REGISTRY_PASSWORD **en masqué**
- CI_REGISTRY_USER **en masqué**
- MYSQL_ROOT_PASSWORD **en masqué**
- MYSQL_USER **en masqué**
- MYSQL_PASSWORD **en masqué**
- vous allez maintenant créer un groupe GitLab nommé `Service On Demand` et mettre le projet `Service On Demand`dedans 
- dans ce groupe, vous allez créer un token de déploiement pour accéder à la Container Registry GitLab : 
- dans le menu Settings -> Repository, développez `Deploy tokens`
- dans ce menu de création, mettez le nom `gitlab-deploy-token`, cochez la case `read_registry` et cliquez sur le bouton `Create Deploy Token`
- notez bien le nom d'utilisateur et le mot de passe ( valeur du token ) générés
- toujours dans ce groupe, vous allez créer des variables de CI/CD pour ce token : 
- CI_DEPLOY_USER **en masqué** : nom d'utilisateur du token
- CI_DEPLOY_PASSWORD **en masqué** : le mot de passe ( valeur ) du token 
- **je précise que le nom du token et le nom de ces 2 variables de CI/CD est très important et doit être celui indiqué**, car GitLab va pouvoir les interpréter pour tous les projets du groupe automatiquement.

Et voilà! Le Service On Demand est prêt. 

Mais comme je l'ai indiqué, il ne peut être exécuté tel quel.
Les projets "clients" pour utiliser ce service à conditions qu'ils fassent partie du groupe `Service On Demand`

# Et après ?
Pour utiliser ce service, vous pouvez retrouver un projet client "type" [disponible ici](https://github.com/alinuxien/service-on-demand-demo-client)
