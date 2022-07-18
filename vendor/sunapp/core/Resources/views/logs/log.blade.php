<div class="sg-app-details">
    <div ref="form_wrapper">
        <div class="sg-detail-header">
            <div class="sg-header-left d-flex align-items-center mb-1">
                <span class="go-back mr-1" @click="hideCard"><i class="feather icon-arrow-left font-medium-4"></i></span>
                <h3 v-if="elementToShow">
                    <span class="text-muted">#@{{ elementToShow.id }}</span>
                    <span class="form-group-translation">
                        <span>@{{ elementToShow.attributes.name + ' [' + elementToShow.attributes.size + ']' }}</span>
                    </span>
                </h3>
            </div>
            <div class="sg-header-right mb-1 ml-2 pl-1">
                <ul class="list-inline m-0">
                </ul>
            </div>
        </div>
        <div class="sg-scroll-area" style="overflow-y: scroll;">
            <div class="row">
                <div v-if="elementToShow && elementToShow.attributes && elementToShow.attributes.content" class="col-12 pt-1">
                    <template v-if="elementToShow.attributes.content.raw">
                        <div v-html="elementToShow.attributes.content.raw"></div>
                    </template>
                    <template v-else-if="elementToShow.attributes.content.laravel">
                        <div v-for="(item, index) in elementToShow.attributes.content.laravel" :key="item.id" :data-id="item.id">
                            <div v-if="item.error == 'INFO'"><hr></div>
                            <div class="row pb-1">
                                <div class="col-1" v-if="item.error == 'ERROR'" style="color: red;">@{{ item.error }}</div>
                                <div class="col-1" v-else>@{{ item.error }}</div>
                                <div class="col-1">@{{ item.context }}</div>
                                <div class="col-2">@{{ item.date }}</div>
                                <div class="col-8">@{{ item.detail }}</div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>
</div>
