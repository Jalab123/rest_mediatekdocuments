<?php
include_once("AccessBDD.php");

/**
 * Classe de construction des requêtes SQL
 * hérite de AccessBDD qui contient les requêtes de base
 * Pour ajouter une requête :
 * - créer la fonction qui crée une requête (prendre modèle sur les fonctions 
 *   existantes qui ne commencent pas par 'traitement')
 * - ajouter un 'case' dans un des switch des fonctions redéfinies 
 * - appeler la nouvelle fonction dans ce 'case'
 */
class MyAccessBDD extends AccessBDD {
	    
    /**
     * constructeur qui appelle celui de la classe mère
     */
    public function __construct(){
        try{
            parent::__construct();
        }catch(\Exception $e){
            throw $e;
        }
    }

    /**
     * demande de recherche
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return array|null tuples du résultat de la requête ou null si erreur
     * @override
     */	
    protected function traitementSelect(string $table, ?array $champs) : ?array{
        switch($table){  
            case "livre" :
                return $this->selectAllLivres();
            case "dvd" :
                return $this->selectAllDvd();
            case "revue" :
                return $this->selectAllRevues();
            case "exemplaire" :
                return $this->selectExemplairesRevue($champs);
            case "genre" :
            case "public" :
            case "rayon" :
            case "etat" :
                // select portant sur une table contenant juste id et libelle
                return $this->selectTableSimple($table);
            case "commande":
                return $this->selectCommandes();
            case "commandedocument" :
                return $this->selectCommandeDocuments($champs);
            case "abonnement":
                return $this->selectAbonnement($champs);
            case "suivi":
                return $this->selectSuivis();
            case "document":
                return $this->selectDocument($champs);
            case "utilisateur":
                return $this->selectUtilisateur($champs);
            case "" :
                // return $this->uneFonction(parametres);
            default:
                // cas général
                return $this->selectTuplesOneTable($table, $champs);
        }	
    }

    /**
     * demande d'ajout (insert)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples ajoutés ou null si erreur
     * @override
     */	
    protected function traitementInsert(string $table, ?array $champs) : ?int{
        switch($table){
            case "" :
                // return $this->uneFonction(parametres);
            case "commandedocument":
                return $this->insertCommandeDocument($champs);
            case "abonnement":
                return $this->insertAbonnement($champs);
            default:                    
                // cas général
                return $this->insertOneTupleOneTable($table, $champs);	
        }
    }
    
    /**
     * demande de modification (update)
     * @param string $table
     * @param string|null $id
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples modifiés ou null si erreur
     * @override
     */	
    protected function traitementUpdate(string $table, ?string $id, ?array $champs) : ?int{
        switch($table){
            case "" :
                // return $this->uneFonction(parametres);
            case "suivi":
                return $this->updateSuivi($champs);
            default:                    
                // cas général
                return $this->updateOneTupleOneTable($table, $id, $champs);
        }	
    }  
    
    /**
     * demande de suppression (delete)
     * @param string $table
     * @param array|null $champs nom et valeur de chaque champ
     * @return int|null nombre de tuples supprimés ou null si erreur
     * @override
     */	
    protected function traitementDelete(string $table, ?array $champs) : ?int{
        switch($table){
            case "" :
                // return $this->uneFonction(parametres);
            case "commande":
                return $this->deleteCommande($champs);
            case "commandedocument":
                return $this->deleteCommandeDocument($champs);
            case "suivi":
                return $this->deleteSuivi($champs);
            default:                    
                // cas général
                return $this->deleteTuplesOneTable($table, $champs);	
        }
    }	    
    
    private function updateSuivi(?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['Id'];
        $champNecessaire['statut'] = $champs['Statut'];
        $requete = "update suivi set ";
        $requete .= "statut=:statut ";			
        $requete .= "where id=:id;";		
        return $this->conn->updateBDD($requete, $champNecessaire);	        
    }
    
    private function deleteCommande(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['Id'];
        $requete = "delete from commande ";
        $requete .= "where id=:id;";  
        return $this->conn->updateBDD($requete, $champNecessaire);	        
    }
    
    private function deleteCommandeDocument(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['Id'];
        $requete = "delete from commandedocument ";
        $requete .= "where id=:id;";  
        return $this->conn->updateBDD($requete, $champNecessaire);	        
    }
    
    private function deleteSuivi(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['Id'];
        $requete = "delete from suivi ";
        $requete .= "where id=:id;";  
        return $this->conn->updateBDD($requete, $champNecessaire);	        
    }
    
    private function selectUtilisateur(?array $champs): ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('login', $champs)){
            return null;
        }
        if(!array_key_exists('pwd', $champs)){
            return null;
        }
        $champNecessaire['login'] = $champs['login'];
        $champNecessaire['pwd'] = $champs['pwd'];
        $requete = "Select idService ";
        $requete .= "from utilisateur ";
        $requete .= "where login = :login AND pwd = :pwd ";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
        
    private function selectCommandeDocuments(?array $champs): ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('idlivredvd', $champs)){
            return null;
        }
        $champNecessaire['idlivredvd'] = $champs['idlivredvd'];
        $requete = "Select cd.id, cd.idLivreDvd, cd.idSuivi, c.dateCommande, c.montant, cd.nbExemplaire, s.statut ";
        $requete .= "from commandedocument cd join suivi s on cd.idSuivi=s.id join commande c on cd.id=c.id ";
        $requete .= "where cd.idLivreDvd = :idlivredvd ";
        $requete .= "order by c.dateCommande DESC ";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    private function selectDocument(?array $champs): ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select * ";
        $requete .= "from document ";
        $requete .= "where id = :id ";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    private function selectAbonnement(?array $champs): ?array{
        if(empty($champs)){
            $requete = "SELECT * FROM abonnement a JOIN document d ON a.idRevue = d.id WHERE dateFinAbonnement BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY dateFinAbonnement; ";
            return $this->conn->queryBDD($requete);      
        }
        if(!array_key_exists('idrevue', $champs)){
            $requete = "SELECT * FROM abonnement a JOIN document d ON a.idRevue = d.id WHERE dateFinAbonnement BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY) ORDER BY dateFinAbonnement; ";
            return $this->conn->queryBDD($requete);
        }
        $champNecessaire['idrevue'] = $champs['idrevue'];
        $requete = "Select * ";
        $requete .= "from abonnement a join commande c on a.id = c.id ";
        $requete .= "where idRevue = :idrevue ";
        $requete .= "order by c.dateCommande DESC ";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }
    
    private function selectCommandes(): ?array{
        $requete = "Select * ";
        $requete .= "from commande ";
        $requete .= "order by CAST(id AS UNSIGNED) ASC ";
        return $this->conn->queryBDD($requete);
    }
    
        private function selectSuivis(): ?array{
        $requete = "Select * ";
        $requete .= "from suivi ";
        $requete .= "order by CAST(id AS UNSIGNED) ASC ";
        return $this->conn->queryBDD($requete);
    }
    
    
    private function insertCommandeDocument(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['Id'];
        $champNecessaire['idlivre'] = $champs['IdLivreDVD'];
        $champNecessaire['nbexemplaire'] = $champs['NbExemplaire'];
        $champNecessaire['idsuivi'] = $champs['IdSuivi'];
        $requete = "insert into commandedocument (id, nbExemplaire, idLivreDvd, idSuivi)";
        $requete .= " values (";
        $requete .= ":id, :nbexemplaire, :idlivre, :idsuivi);";
        return $this->conn->updateBDD($requete, $champNecessaire);
    }
    
    private function insertAbonnement(?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['Id'];
        $champNecessaire['datefinabonnement'] = $champs['DateFinAbonnement'];
        $champNecessaire['idrevue'] = $champs['IdRevue'];
        $requete = "insert into abonnement (id, dateFinAbonnement, idRevue)";
        $requete .= " values (";
        $requete .= ":id, :datefinabonnement, :idrevue);";
        return $this->conn->updateBDD($requete, $champNecessaire);
    }
    
    /**
     * récupère les tuples d'une seule table
     * @param string $table
     * @param array|null $champs
     * @return array|null 
     */
    private function selectTuplesOneTable(string $table, ?array $champs) : ?array{
        if(empty($champs)){
            // tous les tuples d'une table
            $requete = "select * from $table;";
            return $this->conn->queryBDD($requete);  
        }else{
            // tuples spécifiques d'une table
            $requete = "select * from $table where ";
            foreach ($champs as $key => $value){
                $requete .= "$key=:$key and ";
            }
            // (enlève le dernier and)
            $requete = substr($requete, 0, strlen($requete)-5);	          
            return $this->conn->queryBDD($requete, $champs);
        }
    }	

    /**
     * demande d'ajout (insert) d'un tuple dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples ajoutés (0 ou 1) ou null si erreur
     */	
    private function insertOneTupleOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "insert into $table (";
        foreach ($champs as $key => $value){
            $requete .= "$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ") values (";
        foreach ($champs as $key => $value){
            $requete .= ":$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);
        $requete .= ");";
        return $this->conn->updateBDD($requete, $champs);
    }
    
    /**
     * demande de modification (update) d'un tuple dans une table
     * @param string $table
     * @param string\null $id
     * @param array|null $champs 
     * @return int|null nombre de tuples modifiés (0 ou 1) ou null si erreur
     */	
    private function updateOneTupleOneTable(string $table, ?string $id, ?array $champs) : ?int {
        if(empty($champs)){
            return null;
        }
        if(is_null($id)){
            return null;
        }
        // construction de la requête
        $requete = "update $table set ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key,";
        }
        // (enlève la dernière virgule)
        $requete = substr($requete, 0, strlen($requete)-1);				
        $champs["id"] = $id;
        $requete .= " where id=:id;";		
        return $this->conn->updateBDD($requete, $champs);	        
    }
    
    /**
     * demande de suppression (delete) d'un ou plusieurs tuples dans une table
     * @param string $table
     * @param array|null $champs
     * @return int|null nombre de tuples supprimés ou null si erreur
     */
    private function deleteTuplesOneTable(string $table, ?array $champs) : ?int{
        if(empty($champs)){
            return null;
        }
        // construction de la requête
        $requete = "delete from $table where ";
        foreach ($champs as $key => $value){
            $requete .= "$key=:$key and ";
        }
        // (enlève le dernier and)
        $requete = substr($requete, 0, strlen($requete)-5);   
        return $this->conn->updateBDD($requete, $champs);	        
    }
 
    /**
     * récupère toutes les lignes d'une table simple (qui contient juste id et libelle)
     * @param string $table
     * @return array|null
     */
    private function selectTableSimple(string $table) : ?array{
        $requete = "select * from $table order by libelle;";		
        return $this->conn->queryBDD($requete);	    
    }
    
    /**
     * récupère toutes les lignes de la table Livre et les tables associées
     * @return array|null
     */
    private function selectAllLivres() : ?array{
        $requete = "Select l.id, l.ISBN, l.auteur, d.titre, d.image, l.collection, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from livre l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";		
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table DVD et les tables associées
     * @return array|null
     */
    private function selectAllDvd() : ?array{
        $requete = "Select l.id, l.duree, l.realisateur, d.titre, d.image, l.synopsis, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from dvd l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";	
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère toutes les lignes de la table Revue et les tables associées
     * @return array|null
     */
    private function selectAllRevues() : ?array{
        $requete = "Select l.id, l.periodicite, d.titre, d.image, l.delaiMiseADispo, ";
        $requete .= "d.idrayon, d.idpublic, d.idgenre, g.libelle as genre, p.libelle as lePublic, r.libelle as rayon ";
        $requete .= "from revue l join document d on l.id=d.id ";
        $requete .= "join genre g on g.id=d.idGenre ";
        $requete .= "join public p on p.id=d.idPublic ";
        $requete .= "join rayon r on r.id=d.idRayon ";
        $requete .= "order by titre ";
        return $this->conn->queryBDD($requete);
    }	

    /**
     * récupère tous les exemplaires d'une revue
     * @param array|null $champs 
     * @return array|null
     */
    private function selectExemplairesRevue(?array $champs) : ?array{
        if(empty($champs)){
            return null;
        }
        if(!array_key_exists('id', $champs)){
            return null;
        }
        $champNecessaire['id'] = $champs['id'];
        $requete = "Select e.id, e.numero, e.dateAchat, e.photo, e.idEtat ";
        $requete .= "from exemplaire e join document d on e.id=d.id ";
        $requete .= "where e.id = :id ";
        $requete .= "order by e.dateAchat DESC";
        return $this->conn->queryBDD($requete, $champNecessaire);
    }		    
    
}
