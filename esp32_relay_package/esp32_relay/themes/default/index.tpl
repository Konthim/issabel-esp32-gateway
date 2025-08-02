<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-tabs mb-3">
                <li class="nav-item">
                    <a class="nav-link {if !$smarty.get.nav || $smarty.get.nav == 'logs'}active{/if}" 
                       href="?menu={$smarty.get.menu}">Log de Auditoría</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $smarty.get.nav == 'extensions'}active{/if}" 
                       href="?menu={$smarty.get.menu}&nav=extensions">Extensiones Autorizadas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {if $smarty.get.nav == 'config'}active{/if}" 
                       href="?menu={$smarty.get.menu}&nav=config">Configuración</a>
                </li>
            </ul>
            
            {if $smarty.get.msg == 'saved'}
                <div class="alert alert-success">Configuración guardada correctamente</div>
            {/if}
            
            {$CONTENT}
        </div>
    </div>
</div>