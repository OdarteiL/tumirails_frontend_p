import { Component } from '@angular/core';
import { CommonModule } from '@angular/common';
import { ReactiveFormsModule, FormBuilder, FormGroup, Validators } from '@angular/forms';
import { LucideAngularModule, Target, ShieldCheck, BarChart3, Handshake, DollarSign, Award } from 'lucide-angular';

@Component({
  selector: 'app-installers',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule, LucideAngularModule],
  templateUrl: './installers.html',
  styleUrl: './installers.css'
})
export class InstallersComponent {
  readonly Target = Target;
  readonly ShieldCheck = ShieldCheck;
  readonly BarChart3 = BarChart3;
  readonly Handshake = Handshake;
  readonly DollarSign = DollarSign;
  readonly Award = Award;

  applicationForm: FormGroup;

  benefits = [
    {
      title: 'Access Qualified Leads',
      description: 'Connect with a growing network of homeowners and businesses actively seeking solar solutions and energy products.',
      icon: Target
    },
    {
      title: 'Ensure Regulatory Compliance',
      description: "Stay updated with Ghana's energy regulations and integrate seamlessly with Tumirails' compliant marketplace infrastructure.",
      icon: ShieldCheck
    },
    {
      title: 'Gain Performance Analytics',
      description: 'Utilize powerful dashboards to track sales, monitor installation projects, and optimize your business strategy.',
      icon: BarChart3
    },
    {
      title: 'Build Trusted Partnerships',
      description: "Join a community of certified professionals and grow your reputation within Ghana's decentralized energy sector.",
      icon: Handshake
    },
    {
      title: 'Streamlined Payments & Finance',
      description: 'Benefit from secure, efficient payment processing and transparent financial reporting within the Tumirails ecosystem.',
      icon: DollarSign
    },
    {
      title: 'Showcase Expertise & Certifications',
      description: 'Highlight your unique skills and official certifications to attract more high-value clients and build trust.',
      icon: Award
    }
  ];

  constructor(private fb: FormBuilder) {
    this.applicationForm = this.fb.group({
      businessName: ['', Validators.required],
      brn: ['', Validators.required],
      address: ['', Validators.required],
      partnershipType: ['', Validators.required],
      yearsInBusiness: ['', Validators.required],
      contactName: ['', Validators.required],
      email: ['', [Validators.required, Validators.email]],
      phone: ['', Validators.required],
      services: this.fb.group({
        solarInstallation: [false],
        systemMaintenance: [false],
        offGridSolutions: [false],
        energyStorage: [false],
        energyAudits: [false],
        smartHome: [false]
      }),
      certifications: [''],
      additionalInfo: ['']
    });
  }

  onSubmit() {
    if (this.applicationForm.valid) {
      console.log('Application Submitted:', this.applicationForm.value);
      // Logic to handle submission (API call)
      alert('Application submitted successfully!'); // Placeholder for user feedback
      this.applicationForm.reset();
    } else {
      // Mark all fields as touched to show errors
      Object.keys(this.applicationForm.controls).forEach(key => {
        const control = this.applicationForm.get(key);
        control?.markAsTouched();
      });
    }
  }

  // Smooth scroll to form
  scrollToForm() {
    document.getElementById('application-form')?.scrollIntoView({ behavior: 'smooth' });
  }
}
