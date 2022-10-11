<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="card m-auto mt-4">
            <form class="p-2">
                <div class="formBody">
                <p class="mt-2"><span>Main Category </span><span class="asterisk">*</span></p>
                <v-select class="style-chooser" :options="mainCategories" label="name" v-model="selectedMainCategory"></v-select>
                <p class="mt-2"><span>Sub Category </span><span class="asterisk">*</span></p>
                <v-select class="style-chooser" :options="subcategories" label="name" v-model="selectedSubCategory" ></v-select>
                <div v-for="(option,index) in subCatOptions" :key="index">
                <p class="mt-2"><span>{{option.name}}</span></p>
                    <div v-if="selectedSubCategory.id==13&&option.id==2">
                        <v-select @option:selected="()=>getBrandOption(option,selectedOptionsOptions[index])" class="style-chooser" :options="option.options" label="name" v-model="selectedOptionsOptions[index]"></v-select>
                        <input v-show="isVisable(option.options.find(item=>item.name=='other'))" class="vs__dropdown-toggle other" type="text" name="otherContent" v-on:input="()=>getOtherValue(option)" placeholder="enter your option">
                        <div v-for="(child,i) in brandOptions" :key="i">
                            <p class="brand mt-2"><span>{{child.name}}</span></p>
                            <v-select class="style-chooser brand" :options="child.options" label="name" v-model="selectedChild"></v-select>
                            <hr class="horLine" v-if="i+1==brandOptions.length">
                        </div>
                    </div>
                    <div v-else>
                    <v-select @option:selected="()=>getOption(selectedOptionsOptions[index],option)" class="style-chooser" :options="option.options" label="name" v-model="selectedOptionsOptions[index]"></v-select>
                    <input v-show="isVisable(option.options.find(item=>item.name=='other'))" class="vs__dropdown-toggle other" type="text" name="otherContent"  v-on:input="()=>getOtherValue(option)" placeholder="enter your option">
                    </div>
                </div>
                </div>
                <div class="formButton mt-2">
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        Submit
                    </button>
                </div>
            </form>
            </div>
            <!-- Modal -->
            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Options Table</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="MyTable">
                                <thead>
                                <tr>
                                    <th>options name</th>
                                    <th>values</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item,index) in selectedOptionsOptions">
                                    <td>{{subCatOptions[index].name}}</td>
                                    <td v-if="item!=null&&subCatOptions[index].name=='Brand'&&selectedChild!=null">{{item.name}} / {{selectedChild.name}}</td>
                                    <td v-else-if="item!=null&&item.name!='other'">{{item.name}}</td>
                                    <td v-else-if="item!=null&&item.name=='other'">{{item.content}}</td>
                                    <td v-else>Not selected</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    name: "form-component",
    data(){
        return{
            mainCategories:[],
            selectedMainCategory:null,
            selectedSubCategory:null,
            selectedBrand:null,
            selectedChild:null,
            subcategories:[],
            subCatOptions:[],
            brandOptions:[],
            selectedOptionsOptions:[],
        }
    },
    methods:
        {
            isVisable(x)
            {
                return x.show;
            },
            getOption(selected,option)
            {
                let x=option.options.find(item=>item.name=='other');
                if(selected.name=='other')
                    x.show=true;
                else
                    x.show=false;
                this.selectedBrand=selected;
            },
            getBrandOption(option,selected)
            {
                let x=option.options.find(item=>item.name=='other');
                if(selected.name=='other')
                    x.show=true;
                else
                    x.show=false;
                this.selectedBrand=selected;
            },
            getOtherValue(option)
            {
                let val=event.target.value;
                let selector=option.options.find(item=>item.name=='other');
                selector.content=val;
            }
        },
    beforeMount: function () {
        this.mainCategories=[];
        axios
            .get('/api/getAllCategories')
            .then(response => {
                this.mainCategories=response.data.data.categories;
            })
            .catch(error => {
                console.log(error)
            });
    },
    watch:{
        selectedMainCategory:function (newVal)
        {
            if(newVal!=null) {
                this.subcategories=[];
                this.subcategories = newVal.children;
                this.selectedSubCategory=null;
            }
            else
            {
                this.subcategories=[];
                this.subCatOptions=[];
                this.brandOptions=[];
                this.selectedOptionsOptions=[];
                this.selectedSubCategory=null;
                this.selectedBrand=null;
            }
        },

        selectedSubCategory:function (newVal)
        {
            if(newVal!=null)
            {
                this.subCatOptions=[];
                axios
                    .post('/api/getCategoryOption',{selectedSubCat:this.selectedSubCategory.id})
                    .then(response => {
                        this.subCatOptions = response.data.data;
                        this.subCatOptions.forEach((option, index) => {option.options.push({name:"other",show:false,content:""})
                        });
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
            else
            {
                this.subCatOptions=[];
                this.brandOptions=[];
                this.selectedOptionsOptions=[];
            }
        },
        selectedBrand:function (newVal)
        {
            if(newVal!=null)
            {
                this.brandOptions=[];
                this.selectedChild=null;
                axios
                    .get('/api/getOptionChildren/'+newVal.id)
                    .then(response => {
                        this.brandOptions = response.data.data;
                    })
                    .catch(error => {
                        console.log(error)
                    });
            }
            else
            {
                this.brandOptions=[];
                this.selectedOptionsOptions=[];
                this.selectedBrand=null;
                this.selectedChild=null;
            }
        }
    }
}
</script>

<style scoped>
.asterisk{
    color:darkred;
}
.brand{
    margin-left: 2rem;
    margin-right: 2rem;
}
.other{
    margin: 1rem 0 1rem 0;
    width: 100%;
    height: 2rem;
    outline: none;
    /*display: none;*/
}
.formBody{
    overflow: auto;
    height: 30rem;
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.card
{
    width: 30rem;
}
.formBody::-webkit-scrollbar {
    display: none;
}
.horLine{
    margin:1rem;
    opacity: 30%;
    color: grey;
}
 table.MyTable {
     width: 100%;
     background-color: #ffffff;
     border-collapse: collapse;
     border-width: 2px;
     border-color: #a3a3a3;
     border-style: solid;
     color: #000000;
 }

table.MyTable td, table.MyTable th {
    border-width: 2px;
    border-color: #a3a3a3;
    border-style: solid;
    padding: 3px;
}

table.MyTable thead {
    background-color: #fcfcfc;
}
</style>
