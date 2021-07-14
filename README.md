# Bienvenue sur mon projet de création d'un Service On Demand de déploiement d'une application PHP-MySQL sur un cluster Kubernetes avec GitLab
Il s'agit d'un projet réalisé en Juillet 2021 dans le cadre de ma formation "Expert DevOps" chez OpenClassRooms.

## AVERTISSEMENT
Il s'agit seulement d'un projet d'étude, à NE PAS UTILISER EN PROD  !!!

## Ca fait quoi ?
C'est un service de déploiement d'une application PHP sur un cluster Kubernetes, en utilisant GitLab.

Dans le détails, il s'agit de mettre en place un Service On Demand, composé essentiellement d'un Pipeline de CI/CD GitLab, qui pourra être mis à disposition des développeurs, pour leur permettre de développer leur application PHP-MySQL avec GitLab CI/CD, et de la voir déployée automatiquement sur un cluster Kubernetes.

Le Pipeline du Service On Demand n'est pas prévu pour être exécuté directement, mais à la demande, grâce à un trigger ( déclencheur ) depuis un autre Pipeline, celui du développeur.

Lorsque le Pipeline du Service On Demand est déclenché, il va :
- préparer un manisfeste Kubernetes pour la gestion des crédentials MySQL
- préparer une image Docker MySQL et la pousser sur le Docker Hub
- récupérer l'image Docker de l'application du développeur depuis la Container Registry
- envoyer cette image sur le Docker Hub
- créer des objets Kubernetes de `deployment` et de `service` pour l'application PHP et MySQL, sur un cluster distant, en utilisant les images stockées sur le Docker Hub.
- le pod PHP créé peut accéder au pod MySQL
- le pod MySQL crée un stockage "persistant" basique, basé sur un volume nommé

## Ca ressemble à quoi ?
![Vue d'ensemble du Processus du Service On Demand](https://github.com/alinuxien/service-on-demand/blob/master/Service%20On%20Demand.png)

## Contenu ?
- Un pipeline de CI/CD Gitlab : `gitlab-ci.yml` 
- Le dossier `certs` contient tous les fichiers json nécessaires à la création des Certificats pour le Cluster
- Le dossier `terraform` contient les scripts de création des ressources sur AWS
- Le dossier `ansible` contient les playbooks de configuration des ressources sur AWS
- Le fichier `coredns-1.8.yaml` est un manifeste Kubernetes complet pour la configuration du Serveur DNS interne au Cluster
 
## J'ai besoin de quoi ?
- Une VM GitLab locale, avec certains utilitaires, et un Runner de type Shell. Vous pouvez trouver de quoi en créer une sur mesure sur mon projet [gitlab-iac](https://github.com/alinuxien/gitlab-iac)
- Un compte AWS avec un bucket S3 pour stocker les Remote State Terraform. Vous trouverez les instructions si besoin [ici](https://docs.aws.amazon.com/fr_fr/AmazonS3/latest/user-guide/create-bucket.html)
- Un nom de domaine valide pour pouvoir accéder aux pods applicatifs depuis un navigateur web. Ce nom de domaine doit être soit hébergé chez AWS Route 53, soit configuré pour déléguer la gestion à AWS Route 53

## Comment ça s'utilise ?
Chez AWS, pour ceux qui hébergent leur nom de domaine hors AWS Route 53 :

créez une zone hébergée sur votre nom de domaine ( ou sous-domaine ), service Route 53, 
notez les noms des 4 serveurs DNS apparus dans l'enregistrement de type NS, et réalisez la redirection chez votre provider DNS ( 4 enregistrements de type NS aussi, je ne m'étale pas sur ce point )

Chez AWS, pour tous : 
créez un certificat AWS ACM sur votre nom de domaine, service Certificate Manager, avec validation par DNS, avec l'assitance automatique Route 53 ( puisque c'est maintenant lui qui gère le domaine / sous-domaine )

Dans un Terminal : 
- générez une paire de clés SSH qui seront dédiées au Cluster, dans le dossier de votre choix : `ssh-keygen -f chemin-au-choix/nom-de-la-clé-au-choix`

Ensuite,dans GitLab :
- vous devez créer un nouveau projet et y déposer le contenu de ce dépot ( `https://github.com/alinuxien/k8s-aws-iac` )
- éditez le fichier `terraform.tfvars` pour le personnaliser, notamment l'emplacement de la paire de clés ( privée et publique, **en chemin complet** ), le nom de domaine dans la variable `app-domain`, et les types d'instance pour les nodes du Cluster, `k8s-controller-nodes-instance-type` et `k8s-worker-nodes-instance-type` ( j'ai choisi `c5d.xlarge` pour accélérer un peu le process mais `t2.micro` fonctionne très bien, et est beaucoup moins cher :) )
Pour information, Terraform utilise 2 fichiers pour gérer les variables : `vars.tf` pour déclarer les variables et éventuellement leur donner une valeur par défaut, et `terraform.tfvars` pour spécifier la valeur des variables si elles n'ont pas valeur par défaut ou changer la valeur par défaut.
- vous allez créer des variables de CI/CD pour renseigner les crédentials AWS : 
- Dans le projet, allez dans le menu de gauche, Settings -> CI/CD, puis développez les `Variables`, et créez : 
- AWS_ACCESS_KEY_ID **en masqué**
- AWS_SECRET_ACCESS_KEY **en masqué**
- AWS_DEFAULT_REGION ( eu-west-3 par exemple )
- AWS_REGION ( eu-west-3 par exemple )
- AWS_STATE_BUCKET : le nom du bucket S3
- AWS_STATE_KEY : un nom au choix, comme le nom du projet, qui sera utilisé comme racine pour le nom des objets de Remote State Terraform
- TF_IN_AUTOMATION : true

Et voilà! L'environnement de travail est prêt. Il suffit d'exécuter le pipeline : 
- dans GitLab, menu de gauche, CI/CD -> Pipelines
- cliquez sur le bouton bleu en haut à droite `Run Pipeline`
- le reste est automatique : GitLab va faire des vérification, planifier, et créer le Cluster. 
- la dernière étape ( sur 4 ), sert à détruire le Cluster sur AWS, et est manuelle, pour que vous puissiez en profiter un peu d'abord... D'ailleurs, pensez bien à détruire le Cluster, dans tous les cas, pour ne pas allourdir votre facture AWS inutilement.

Quand le Cluster est créé, et l'accès est configuré pour l'utilisateur `vagrant` dans la VM. Pour le tester, dans un terminal :
- `kubectl version` 
- `kubectl cluster-info`
- `kubectl get nodes` liste les noeuds enregistrés dans le cluster
- `kubectl get all -A` liste tous les objets Kubernetes existant dans le cluster, sur tous les namespaces

# Et après ?
Pour jouer avec ce Cluster, je vous propose la suite du projet, qui consiste à mettre en place un Service On Demand, capable de déployer des applications PHP-MySQL depuis GitLab vers le Cluster, sous forme de pods, de deployments et de services, et [disponible ici](https://github.com/alinuxien/service-on-demand)
