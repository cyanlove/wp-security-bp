<template>
	<div class="container">
		<div class="header">
			<h1>WordPress Security Best Practices</h1>
		</div>
		<div class="separator"></div>
		<div class="section-progress">
			<!-- attributes: https://vue-multiple.github.io/progress/#attributes -->
			<VmProgress 
				 :percentage="percentage" 
				 type="circle" 
				 stroke-color="#393939" 
				 stroke-linecap="square"
				 :width="200"
				 stroke-width="25"
			 >
	 		</VmProgress>
		</div>
		<div class="separator"></div>
		<div class="section-checks">
			<checkblock 
				v-for="( checkinfo, index ) in info" 
				:checkinfo="checkinfo" 
				:key="index"
				@fix="fix"
			>
			</checkblock>
		</div>
	</div>
</template>
<script>
import checkblock from './checkblock.vue';
import VmProgress from 'vue-multiple-progress'
import axios from 'axios';
export default{
	data() {
		return {
			info: [],
			percentage: '',
		}
	},
	components:{
		checkblock,
		VmProgress
	},
	mounted() {
		this.getInfo()
	},
	methods:{
		getInfo(){
			//Get all checks responses in json
			var params = new URLSearchParams();
			params.append('action', 'check-all');
			params.append('nonce', wpsbp.nonce);

			axios.post(ajaxurl, params)
			.then( response => {
				this.info = response.data;
				//trigger calculate percentage function
				this.calc_percentage()
				console.log(this.info)
			})
			.catch( error => {
				console.log(error);
			});
		},
		fix(e){
			//Fix action
			var params = new URLSearchParams();
			params.append('action', e);
			params.append('nonce', wpsbp.nonce);

			axios.post(ajaxurl, params)
			.then( response => {
				this.info = response.data;
				//trigger calculate percentage function
				this.calc_percentage()
				console.log(this.info)
			})
			.catch( error => {
				console.log(error);
			});
		},
		calc_percentage(){
			/*calculate percentage of passed checks*/
			const map_info_status = this.info.map( x => x.status )
			const count_pass      = map_info_status.filter( pass => pass === 'pass')
			this.percentage       = Math.round(( 100 / map_info_status.length ) * count_pass.length)
		}
	}
}
</script> 