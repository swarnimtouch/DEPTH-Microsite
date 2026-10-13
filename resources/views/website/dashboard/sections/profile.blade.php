<section class="view wrap" data-view="profile">
  <div class="member-head"><div><div class="kicker">My profile</div><h1 class="h1" id="pf-name">Profile</h1></div></div>
  <div class="card pad profile-form-card">
    @if($errors->any())
      <div class="profile-errors"><b>Please correct the highlighted details.</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="profile_context" value="dashboard">
      <div class="profile-edit-grid">
        <label class="field"><span>Full name <em>*</em></span><input class="input" type="text" name="name" value="{{ old('name', $doctor->name) }}" required></label>
        <label class="field"><span>Speciality <em>*</em></span><select class="input" name="speciality_id" required><option value="">Select speciality</option>@foreach($specialities as $speciality)<option value="{{ $speciality->id }}" @selected((string) old('speciality_id', $doctor->speciality_id) === (string) $speciality->id)>{{ $speciality->name }}</option>@endforeach</select></label>
        <label class="field"><span>Institution / Hospital <em>*</em></span><input class="input" type="text" name="hospital" value="{{ old('hospital', $doctor->hospital) }}" required></label>
        <label class="field"><span>Country <em>*</em></span><select class="input" name="country" required><option value="">Select country</option>@foreach($countries as $country)<option value="{{ $country->name }}" @selected(old('country', $doctor->country) === $country->name)>{{ $country->name }}</option>@endforeach</select></label>
        <label class="field"><span>City</span><input class="input" type="text" name="city" value="{{ old('city', $doctor->city) }}"></label>
        <label class="field"><span>Mobile number <em>*</em></span><input class="input" type="tel" name="mobile" value="{{ old('mobile', $doctor->mobile) }}" placeholder="+919876543210" required></label>
        <label class="field span2"><span>Email ID <em>*</em></span><input class="input" type="email" name="email" value="{{ old('email', $doctor->email) }}" required></label>
      </div>
      <div class="profile-form-actions"><button class="btn btn-grad" type="submit">Save profile</button></div>
    </form>
  </div>
</section>
