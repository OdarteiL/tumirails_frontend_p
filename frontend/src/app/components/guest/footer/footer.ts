import { Component } from '@angular/core';
import { RouterLink } from '@angular/router';
import { FormsModule } from '@angular/forms';
import { LucideAngularModule, Zap, Facebook, Twitter, Linkedin, Youtube } from 'lucide-angular';

@Component({
  selector: 'app-footer',
  standalone: true,
  imports: [RouterLink, FormsModule, LucideAngularModule],
  templateUrl: './footer.html',
  styleUrl: './footer.css'
})
export class FooterComponent {
  currentYear = new Date().getFullYear();
  newsletterEmail = '';

  readonly Zap = Zap;
  readonly Facebook = Facebook;
  readonly Twitter = Twitter;
  readonly Linkedin = Linkedin;
  readonly Youtube = Youtube;

  onNewsletterSubmit(): void {
    if (this.newsletterEmail) {
      // TODO: Implement newsletter subscription logic
      console.log('Newsletter subscription:', this.newsletterEmail);
      // Show success message
      alert('Thank you for subscribing to our newsletter!');
      this.newsletterEmail = '';
    }
  }
}
